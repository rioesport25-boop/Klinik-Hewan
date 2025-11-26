<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch, nextTick, onBeforeUnmount, onMounted, computed } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { showConfirm, showSuccess } from '@/Plugins/sweetalert';

const page = usePage();

defineProps({
    addresses: {
        type: Array,
        default: () => [],
    },
});

const showAddressModal = ref(false);
const showMapModal = ref(false);
const showDetailModal = ref(false);
const showEditModal = ref(false);
const showEditMapModal = ref(false);
const searchQuery = ref('');
const currentLocation = ref(null);
const editLocation = ref(null);
const selectedAddress = ref({
    name: '',
    latitude: null,
    longitude: null,
    full_address: '',
    city: '',
    province: '',
    district: '',
    postal_code: '',
    label: 'Rumah',
    recipient_name: '',
    phone_number: '',
});
const editingAddress = ref(null);

const mapElement = ref(null);
const editMapElement = ref(null);
const mapInstance = ref(null);
const editMapInstance = ref(null);
const mapLoading = ref(false);
let mapUpdateTimeout = null;
let editMapUpdateTimeout = null;

const DEFAULT_COORDS = { lat: -6.2088, lng: 106.8456 };
const MAP_PAN_STEP = 0.001; // ~111m per step

const reverseGeocode = async (lat, lng) => {
    const response = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
    );
    return response.json();
};

const updateSelectedAddressFromCoords = async (coords) => {
    if (!coords) return;

    try {
        const data = await reverseGeocode(coords.lat, coords.lng);
        selectedAddress.value.latitude = coords.lat;
        selectedAddress.value.longitude = coords.lng;
        selectedAddress.value.name = data.display_name || 'Lokasi Terpilih';
        selectedAddress.value.full_address = data.display_name || '';
        
        // Auto-fill administrative fields from geocoding
        if (data.address) {
            selectedAddress.value.province = data.address.state || '';
            selectedAddress.value.city = data.address.city || data.address.town || data.address.county || '';
            selectedAddress.value.district = data.address.suburb || data.address.neighbourhood || data.address.village || '';
            selectedAddress.value.postal_code = data.address.postcode || '';
        }
    } catch (error) {
        console.error('Error fetching address:', error);
    }
};

const updateEditingAddressFromCoords = async (coords) => {
    if (!coords || !editingAddress.value) return;

    try {
        const data = await reverseGeocode(coords.lat, coords.lng);
        editingAddress.value.latitude = coords.lat;
        editingAddress.value.longitude = coords.lng;
        editingAddress.value.full_address = data.display_name || editingAddress.value.full_address;

        if (data.address) {
            editingAddress.value.province = data.address.state || editingAddress.value.province;
            editingAddress.value.city = data.address.city || data.address.town || data.address.county || editingAddress.value.city;
            editingAddress.value.district = data.address.suburb || data.address.neighbourhood || data.address.village || editingAddress.value.district;
            editingAddress.value.postal_code = data.address.postcode || editingAddress.value.postal_code;
        }
    } catch (error) {
        console.error('Error fetching address:', error);
    }
};

const openAddressModal = () => {
    showAddressModal.value = true;
};

const closeAddressModal = () => {
    showAddressModal.value = false;
    searchQuery.value = '';
};

const closeMapModal = () => {
    showMapModal.value = false;
    currentLocation.value = null;
};

const useCurrentLocation = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const coords = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };

                currentLocation.value = coords;
                await updateSelectedAddressFromCoords(coords);

                showAddressModal.value = false;
                showMapModal.value = true;
            },
            (error) => {
                console.error('Error getting location:', error);
                page.props.flash = { error: 'Tidak dapat mengakses lokasi. Pastikan Anda mengizinkan akses lokasi.' };
            }
        );
    } else {
        page.props.flash = { error: 'Browser Anda tidak mendukung geolocation.' };
    }
};

const updateAddressFromLocation = async (coords = currentLocation.value) => {
    if (!coords) return;
    await updateSelectedAddressFromCoords(coords);
};

const searchLocation = () => {
    if (searchQuery.value.trim()) {
        console.log('Searching for:', searchQuery.value);
        // TODO: Implement location search functionality
    }
};

watch(showMapModal, async (value) => {
    if (value) {
        if (!currentLocation.value) {
            currentLocation.value = selectedAddress.value.latitude && selectedAddress.value.longitude
                ? { lat: selectedAddress.value.latitude, lng: selectedAddress.value.longitude }
                : DEFAULT_COORDS;
        }
        await nextTick();
        initializeMap();
    } else {
        destroyMap();
    }
});

watch(showEditMapModal, async (value) => {
    if (value) {
        await nextTick();
        initializeEditMap();
    } else {
        destroyEditMap();
    }
});

onBeforeUnmount(() => {
    destroyMap();
    destroyEditMap();
});

const destroyMap = () => {
    if (mapUpdateTimeout) {
        clearTimeout(mapUpdateTimeout);
        mapUpdateTimeout = null;
    }
    if (mapInstance.value) {
        mapInstance.value.remove();
        mapInstance.value = null;
    }
};

const destroyEditMap = () => {
    if (editMapUpdateTimeout) {
        clearTimeout(editMapUpdateTimeout);
        editMapUpdateTimeout = null;
    }
    if (editMapInstance.value) {
        editMapInstance.value.remove();
        editMapInstance.value = null;
    }
};

const initializeMap = () => {
    try {
        if (!mapElement.value) return;

        const initialCoords = currentLocation.value || DEFAULT_COORDS;
        currentLocation.value = initialCoords;

        destroyMap();
        
        mapLoading.value = true;
        
        mapInstance.value = L.map(mapElement.value, {
            center: [initialCoords.lat, initialCoords.lng],
            zoom: 16,
            zoomControl: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(mapInstance.value);

        mapInstance.value.on('moveend', () => {
            const center = mapInstance.value.getCenter();
            const coords = { lat: center.lat, lng: center.lng };
            currentLocation.value = coords;
            
            if (mapUpdateTimeout) clearTimeout(mapUpdateTimeout);
            mapUpdateTimeout = setTimeout(() => updateAddressFromLocation(coords), 500);
        });

        mapLoading.value = false;
        updateAddressFromLocation(initialCoords);
    } catch (error) {
        console.error('Error initializing map:', error);
        mapLoading.value = false;
    }
};

const initializeEditMap = () => {
    if (!editingAddress.value) return;

    try {
        if (!editMapElement.value) return;

        const initialCoords = editLocation.value
            || (editingAddress.value.latitude && editingAddress.value.longitude
                ? { lat: editingAddress.value.latitude, lng: editingAddress.value.longitude }
                : DEFAULT_COORDS);

        editLocation.value = initialCoords;

        destroyEditMap();
        
        mapLoading.value = true;

        editMapInstance.value = L.map(editMapElement.value, {
            center: [initialCoords.lat, initialCoords.lng],
            zoom: 16,
            zoomControl: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(editMapInstance.value);

        editMapInstance.value.on('moveend', () => {
            const center = editMapInstance.value.getCenter();
            const coords = { lat: center.lat, lng: center.lng };
            editLocation.value = coords;
            
            if (editMapUpdateTimeout) clearTimeout(editMapUpdateTimeout);
            editMapUpdateTimeout = setTimeout(() => updateEditingAddressFromCoords(coords), 500);
        });

        mapLoading.value = false;
        updateEditingAddressFromCoords(initialCoords);
    } catch (error) {
        console.error('Error initializing edit map:', error);
        mapLoading.value = false;
    }
};

const moveMapCenter = (latOffset, lngOffset) => {
    if (!mapInstance.value) return;
    const center = mapInstance.value.getCenter();
    mapInstance.value.panTo([center.lat + latOffset, center.lng + lngOffset]);
};

const moveEditMapCenter = (latOffset, lngOffset) => {
    if (!editMapInstance.value) return;
    const center = editMapInstance.value.getCenter();
    editMapInstance.value.panTo([center.lat + latOffset, center.lng + lngOffset]);
};

const saveAddressDetails = () => {
    // Move from map modal to detail modal
    showMapModal.value = false;
    showDetailModal.value = true;
    
    // Pre-fill recipient name and phone from user if available
    const user = page.props.auth.user;
    if (!selectedAddress.value.recipient_name && user) {
        selectedAddress.value.recipient_name = user.name || '';
    }
};

const closeDetailModal = () => {
    showDetailModal.value = false;
};

const form = useForm({
    label: 'Rumah',
    recipient_name: '',
    phone_number: '',
    full_address: '',
    city: '',
    province: '',
    district: '',
    postal_code: '',
    latitude: null,
    longitude: null,
    is_default: false,
});

const submitAddress = () => {
    form.label = selectedAddress.value.label;
    form.recipient_name = selectedAddress.value.recipient_name;
    form.phone_number = selectedAddress.value.phone_number;
    form.full_address = selectedAddress.value.full_address;
    form.city = selectedAddress.value.city;
    form.province = selectedAddress.value.province;
    form.district = selectedAddress.value.district;
    form.postal_code = selectedAddress.value.postal_code;
    form.latitude = selectedAddress.value.latitude;
    form.longitude = selectedAddress.value.longitude;
    
    form.post(route('profile.addresses.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeDetailModal();
            selectedAddress.value = {
                name: '',
                latitude: null,
                longitude: null,
                full_address: '',
                city: '',
                province: '',
                district: '',
                postal_code: '',
                label: 'Rumah',
                recipient_name: '',
                phone_number: '',
            };
            form.reset();
        },
    });
};

const setDefaultAddress = (addressId) => {
    router.post(route('profile.addresses.set-default', addressId), {}, {
        preserveScroll: true,
    });
};

const deleteAddress = (addressId) => {
    showConfirm({
        title: 'Hapus Alamat?',
        text: 'Apakah Anda yakin ingin menghapus alamat ini?',
        icon: 'warning',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('profile.addresses.destroy', addressId), {
                preserveScroll: true,
                onSuccess: () => {
                    showSuccess('Alamat berhasil dihapus');
                }
            });
        }
    });
};

const editAddress = (address) => {
    editingAddress.value = { ...address };
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingAddress.value = null;
};

const updateAddress = () => {
    if (!editingAddress.value) return;
    
    router.patch(route('profile.addresses.update', editingAddress.value.id), {
        label: editingAddress.value.label,
        recipient_name: editingAddress.value.recipient_name,
        phone_number: editingAddress.value.phone_number,
        full_address: editingAddress.value.full_address,
        city: editingAddress.value.city,
        province: editingAddress.value.province,
        postal_code: editingAddress.value.postal_code,
        latitude: editingAddress.value.latitude,
        longitude: editingAddress.value.longitude,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const openEditMapModal = () => {
    if (editingAddress.value) {
        // Ensure we have valid coordinates
        const lat = editingAddress.value.latitude || -6.2088; // Default to Jakarta if null
        const lng = editingAddress.value.longitude || 106.8456;
        
        editLocation.value = {
            lat: lat,
            lng: lng
        };
        showEditMapModal.value = true;
    }
};

const closeEditMapModal = () => {
    showEditMapModal.value = false;
    editLocation.value = null;
};

const saveEditLocation = async () => {
    if (!editLocation.value || !editingAddress.value) return;
    
    // Update coordinates
    editingAddress.value.latitude = editLocation.value.lat;
    editingAddress.value.longitude = editLocation.value.lng;
    
    // Get address from coordinates using reverse geocoding
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${editLocation.value.lat}&lon=${editLocation.value.lng}&zoom=18&addressdetails=1`
        );
        const data = await response.json();
        
        // Update full address
        editingAddress.value.full_address = data.display_name || editingAddress.value.full_address;
        
        // Parse address components if available
        if (data.address) {
            editingAddress.value.province = data.address.state || editingAddress.value.province;
            editingAddress.value.city = data.address.city || data.address.town || data.address.county || editingAddress.value.city;
            editingAddress.value.postal_code = data.address.postcode || editingAddress.value.postal_code;
        }
    } catch (error) {
        console.error('Error fetching address:', error);
    }
    
    closeEditMapModal();
};

const useCurrentLocationForEdit = () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                editLocation.value = {
                    lat: lat,
                    lng: lng
                };
                
                if (editMapInstance.value) {
                    editMapInstance.value.panTo([lat, lng]);
                }
                
                // Update editing address
                if (editingAddress.value) {
                    editingAddress.value.latitude = lat;
                    editingAddress.value.longitude = lng;
                    
                    // Get address from coordinates
                    try {
                        const response = await fetch(
                            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
                        );
                        const data = await response.json();
                        
                        editingAddress.value.full_address = data.display_name || '';
                        
                        if (data.address) {
                            editingAddress.value.province = data.address.state || editingAddress.value.province;
                            editingAddress.value.city = data.address.city || data.address.town || data.address.county || editingAddress.value.city;
                            editingAddress.value.postal_code = data.address.postcode || editingAddress.value.postal_code;
                        }
                    } catch (error) {
                        console.error('Error fetching address:', error);
                    }
                }
            },
            (error) => {
                console.error('Error getting location:', error);
                page.props.flash = { error: 'Tidak dapat mengakses lokasi. Pastikan Anda mengizinkan akses lokasi.' };
            }
        );
    } else {
        page.props.flash = { error: 'Browser Anda tidak mendukung geolocation.' };
    }
};
</script>

<template>
    <AppLayout title="Daftar Alamat">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Daftar Alamat
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-4">
                                <Link
                                    :href="route('home')"
                                    class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors"
                                    title="Kembali ke Home"
                                >
                                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                </Link>
                                <div>
                                    <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                                        Daftar Alamat Pengiriman
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Kelola alamat pengiriman Anda untuk checkout yang lebih cepat
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="openAddressModal"
                                class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                            >
                                <svg class="-ml-0.5 mr-1.5 size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Tambah Alamat
                            </button>
                        </div>

                        <div v-if="addresses.length === 0" class="text-center py-12">
                            <svg class="mx-auto size-24 text-gray-400 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                                Belum ada alamat tersimpan
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Tambahkan alamat pengiriman untuk mempermudah checkout
                            </p>
                        </div>

                        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="address in addresses"
                                :key="address.id"
                                class="relative rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900"
                            >
                                <!-- Label Badge -->
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                        {{ address.label }}
                                    </span>
                                    <span
                                        v-if="address.is_default"
                                        class="inline-flex items-center text-amber-600 dark:text-amber-400"
                                    >
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                </div>

                                <!-- Recipient Info -->
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ address.recipient_name }}
                                </h4>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ address.phone_number }}
                                </p>

                                <!-- Address -->
                                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                    {{ address.full_address }}
                                </p>

                                <!-- Action Buttons -->
                                <div class="mt-4 flex gap-3">
                                    <button
                                        @click="editAddress(address)"
                                        class="text-sm font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300"
                                    >
                                        Ubah
                                    </button>
                                    <button
                                        v-if="!address.is_default"
                                        @click="setDefaultAddress(address.id)"
                                        class="text-sm font-medium text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300"
                                    >
                                        Pilih Alamat
                                    </button>
                                    <button
                                        @click="deleteAddress(address.id)"
                                        class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Cari Alamat -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showAddressModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    @click.self="closeAddressModal"
                >
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75"></div>

                    <!-- Modal Container -->
                    <div class="flex min-h-full items-center justify-center p-4">
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <div
                                v-if="showAddressModal"
                                class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-xl transition-all dark:bg-gray-800"
                            >
                                <!-- Header -->
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Cari Alamat
                                    </h3>
                                    <button
                                        type="button"
                                        @click="closeAddressModal"
                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                    >
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Search Input -->
                                <div class="mb-4">
                                    <div class="relative">
                                        <input
                                            v-model="searchQuery"
                                            type="text"
                                            placeholder="Cari lokasi/gedung/nama jalan"
                                            class="block w-full rounded-lg border border-gray-300 bg-white py-3 pl-4 pr-12 text-sm text-gray-900 placeholder:text-gray-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-amber-500 dark:focus:ring-amber-500"
                                            @keyup.enter="searchLocation"
                                        >
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                            <svg class="size-5 text-amber-500 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Use Current Location Button -->
                                <button
                                    type="button"
                                    @click="useCurrentLocation"
                                    class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <svg class="size-5 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                                    </svg>
                                    <span>Gunakan lokasi saat ini</span>
                                </button>

                                <!-- Info Text -->
                                <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                                    Silakan masukkan alamat/lokasimu untuk menentukkan lokasi pengiriman
                                </p>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Modal Tandai Titik di Peta -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showMapModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                >
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75"></div>

                    <!-- Modal Container -->
                    <div class="flex min-h-full items-end justify-center sm:items-center">
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <div
                                v-if="showMapModal"
                                class="relative w-full max-w-2xl transform overflow-hidden rounded-t-3xl bg-white text-left shadow-xl transition-all sm:rounded-2xl dark:bg-gray-800"
                            >
                                <!-- Header -->
                                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                    <button
                                        type="button"
                                        @click="closeMapModal"
                                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                    >
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                        </svg>
                                    </button>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Tandai Titik di Peta
                                    </h3>
                                    <button
                                        type="button"
                                        @click="closeMapModal"
                                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                    >
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Map Container -->
                                <div class="relative h-96 w-full bg-gray-200 dark:bg-gray-700">
                                    <div ref="mapElement" class="h-full w-full z-0"></div>
                                    <div
                                        v-if="mapLoading"
                                        class="absolute inset-0 flex items-center justify-center bg-gray-200/80 dark:bg-gray-700/80 z-10"
                                    >
                                        <span class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow dark:bg-gray-800 dark:text-gray-200">
                                            Memuat peta...
                                        </span>
                                    </div>

                                    <!-- Center Pin Overlay -->
                                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center z-20">
                                        <div class="relative -mt-5">
                                            <svg class="size-12 text-red-500 drop-shadow-lg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Direction Controls -->
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-30">
                                        <div class="relative w-full h-full">
                                            <!-- Up -->
                                            <button
                                                type="button"
                                                @click="moveMapCenter(MAP_PAN_STEP, 0)"
                                                class="pointer-events-auto absolute top-4 left-1/2 -translate-x-1/2 rounded-lg bg-white p-3 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                                            >
                                                <svg class="size-5 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                                </svg>
                                            </button>
                                            <!-- Down -->
                                            <button
                                                type="button"
                                                @click="moveMapCenter(-MAP_PAN_STEP, 0)"
                                                class="pointer-events-auto absolute bottom-4 left-1/2 -translate-x-1/2 rounded-lg bg-white p-3 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                                            >
                                                <svg class="size-5 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                            <!-- Left -->
                                            <button
                                                type="button"
                                                @click="moveMapCenter(0, -MAP_PAN_STEP)"
                                                class="pointer-events-auto absolute top-1/2 left-4 -translate-y-1/2 rounded-lg bg-white p-3 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                                            >
                                                <svg class="size-5 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                                </svg>
                                            </button>
                                            <!-- Right -->
                                            <button
                                                type="button"
                                                @click="moveMapCenter(0, MAP_PAN_STEP)"
                                                class="pointer-events-auto absolute top-1/2 right-4 -translate-y-1/2 rounded-lg bg-white p-3 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                                            >
                                                <svg class="size-5 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address Details Form -->
                                <div class="p-6 space-y-4">
                                    <!-- Instructions -->
                                    <div class="rounded-lg bg-amber-50 p-4 dark:bg-amber-900/20">
                                        <div class="flex gap-3">
                                            <svg class="size-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                            </svg>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-amber-900 dark:text-amber-200">
                                                    Sesuaikan titik lokasi dengan tombol arah
                                                </p>
                                                <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                                    Gunakan tombol panah untuk menggeser peta. Pin merah menunjukkan lokasi yang akan disimpan.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Location Name -->
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Area</p>
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white break-words">
                                                {{ selectedAddress.name || 'Lokasi Terpilih' }}
                                            </h4>
                                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 break-words">
                                                {{ selectedAddress.full_address || 'Memuat alamat...' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Label Input -->
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Label Alamat
                                        </label>
                                        <div class="flex gap-2">
                                            <button
                                                type="button"
                                                @click="selectedAddress.label = 'Rumah'"
                                                :class="[
                                                    'flex-1 rounded-lg border px-4 py-2 text-sm font-medium transition',
                                                    selectedAddress.label === 'Rumah'
                                                        ? 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'
                                                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                                                ]"
                                            >
                                                Rumah
                                            </button>
                                            <button
                                                type="button"
                                                @click="selectedAddress.label = 'Kantor'"
                                                :class="[
                                                    'flex-1 rounded-lg border px-4 py-2 text-sm font-medium transition',
                                                    selectedAddress.label === 'Kantor'
                                                        ? 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'
                                                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                                                ]"
                                            >
                                                Kantor
                                            </button>
                                            <button
                                                type="button"
                                                @click="selectedAddress.label = 'Lainnya'"
                                                :class="[
                                                    'flex-1 rounded-lg border px-4 py-2 text-sm font-medium transition',
                                                    selectedAddress.label === 'Lainnya'
                                                        ? 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'
                                                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                                                ]"
                                            >
                                                Lainnya
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Continue Button -->
                                    <button
                                        type="button"
                                        @click="saveAddressDetails"
                                        class="w-full rounded-lg bg-amber-600 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                    >
                                        Pilih Titik Lokasi
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Modal Detail Alamat -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showDetailModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                >
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75"></div>

                    <!-- Modal Container -->
                    <div class="flex min-h-full items-center justify-center p-4">
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <div
                                v-if="showDetailModal"
                                class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-xl transition-all dark:bg-gray-800"
                            >
                                <!-- Header -->
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Detail Alamat
                                    </h3>
                                    <button
                                        type="button"
                                        @click="closeDetailModal"
                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                    >
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Form -->
                                <form @submit.prevent="submitAddress" class="space-y-4">
                                    <!-- Recipient Name -->
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nama Penerima <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="selectedAddress.recipient_name"
                                            type="text"
                                            required
                                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-amber-500 dark:focus:ring-amber-500"
                                            placeholder="Masukkan nama penerima"
                                        >
                                    </div>

                                    <!-- Phone Number -->
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Nomor Telepon <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="selectedAddress.phone_number"
                                            type="tel"
                                            required
                                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-amber-500 dark:focus:ring-amber-500"
                                            placeholder="08xxxxxxxxxx"
                                        >
                                    </div>

                                    <!-- Auto-filled Administrative Fields -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <!-- Province -->
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Provinsi <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="selectedAddress.province"
                                                type="text"
                                                required
                                                readonly
                                                class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300"
                                                placeholder="Terisi otomatis"
                                            >
                                        </div>

                                        <!-- City -->
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Kota/Kabupaten <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="selectedAddress.city"
                                                type="text"
                                                required
                                                readonly
                                                class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300"
                                                placeholder="Terisi otomatis"
                                            >
                                        </div>

                                        <!-- District -->
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Kecamatan
                                            </label>
                                            <input
                                                v-model="selectedAddress.district"
                                                type="text"
                                                readonly
                                                class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300"
                                                placeholder="Terisi otomatis"
                                            >
                                        </div>

                                        <!-- Postal Code -->
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Kode Pos <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="selectedAddress.postal_code"
                                                type="text"
                                                required
                                                readonly
                                                class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300"
                                                placeholder="Terisi otomatis"
                                            >
                                        </div>
                                    </div>

                                    <!-- Info Box -->
                                    <div class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20">
                                        <div class="flex gap-2">
                                            <svg class="size-5 text-amber-600 dark:text-amber-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                            </svg>
                                            <p class="text-xs text-amber-800 dark:text-amber-200">
                                                Field Provinsi, Kota, Kecamatan, dan Kode Pos terisi otomatis dari titik lokasi yang Anda pilih di peta.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Address Preview -->
                                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                                        <div class="flex items-start gap-3">
                                            <div class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900/30">
                                                <svg class="size-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Alamat</p>
                                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                                    {{ selectedAddress.full_address }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="w-full rounded-lg bg-amber-600 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-800"
                                    >
                                        {{ form.processing ? 'Menyimpan...' : 'Simpan Alamat' }}
                                    </button>
                                </form>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Modal Edit Alamat -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showEditModal && editingAddress"
                    class="fixed inset-0 z-50 overflow-y-auto"
                >
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75" @click="closeEditModal"></div>

                    <!-- Modal Container -->
                    <div class="flex min-h-full items-center justify-center p-4">
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <div
                                v-if="showEditModal && editingAddress"
                                class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-xl transition-all dark:bg-gray-800"
                            >
                                <!-- Header -->
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Ubah Alamat
                                    </h3>
                                    <button
                                        type="button"
                                        @click="closeEditModal"
                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                    >
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Edit Form -->
                                <form @submit.prevent="updateAddress" class="space-y-4">
                                    <!-- Label Alamat -->
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Label Alamat <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="editingAddress.label"
                                            type="text"
                                            required
                                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                            
                                        >
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Contoh: Rumah, apartmen, atau kantor</p>
                                    </div>

                                    <!-- Titik Lokasi -->
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Titik Lokasi <span class="text-red-500">*</span>
                                        </label>
                                        <div class="rounded-lg border border-gray-300 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-900">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0 rounded-lg bg-red-50 p-2 dark:bg-red-900/30">
                                                    <svg class="size-5 text-red-500 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ editingAddress.full_address?.split(',')[0] || 'Jatiraden' }}</p>
                                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 break-words">
                                                        {{ editingAddress.full_address }}
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="openEditMapModal"
                                                    class="text-sm font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400 flex-shrink-0"
                                                >
                                                    Ubah
                                                </button>
                                            </div>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Untuk mempermudah pengiriman, pastikan titik lokasi kamu sudah tepat ya</p>
                                    </div>

                                    <!-- Alamat Lengkap -->
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Alamat Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="editingAddress.full_address"
                                            required
                                            rows="3"
                                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                            
                                        ></textarea>
                                    </div>

                                    <!-- Row: Provinsi & Kota -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Provinsi <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="editingAddress.province"
                                                type="text"
                                                required
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                
                                            >
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Kota <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="editingAddress.city"
                                                type="text"
                                                required
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                
                                            >
                                        </div>
                                    </div>

                                    <!-- Row: Kecamatan & Kelurahan -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Kecamatan <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                required
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                
                                            >
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Kelurahan <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                required
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                
                                            >
                                        </div>
                                    </div>

                                    <!-- Kode Pos -->
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Kode Pos <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="editingAddress.postal_code"
                                            type="text"
                                            required
                                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            
                                        >
                                    </div>

                                    <!-- Row: Nama Penerima & Nomor HP -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Nama Penerima <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="editingAddress.recipient_name"
                                                type="text"
                                                required
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                
                                            >
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Nomor Handphone <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="editingAddress.phone_number"
                                                type="tel"
                                                required
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                
                                            >
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <button
                                        type="submit"
                                        class="w-full rounded-lg bg-amber-600 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                    >
                                        Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Modal Edit Map -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showEditMapModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                >
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity dark:bg-gray-900 dark:bg-opacity-75"></div>

                    <!-- Modal Container -->
                    <div class="flex min-h-full items-end justify-center sm:items-center">
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <div
                                v-if="showEditMapModal"
                                class="relative w-full max-w-2xl transform overflow-hidden rounded-t-3xl bg-white text-left shadow-xl transition-all sm:rounded-2xl dark:bg-gray-800"
                            >
                                <!-- Header -->
                                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                    <button
                                        type="button"
                                        @click="closeEditMapModal"
                                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                    >
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                        </svg>
                                    </button>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Atur Titik Lokasi
                                    </h3>
                                    <button
                                        type="button"
                                        @click="closeEditMapModal"
                                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                    >
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Map Container -->
                                <div class="relative h-96 w-full bg-gray-200 dark:bg-gray-700">
                                    <div ref="editMapElement" class="h-full w-full z-0"></div>
                                    <div
                                        v-if="mapLoading"
                                        class="absolute inset-0 flex items-center justify-center bg-gray-200/80 dark:bg-gray-700/80 z-10"
                                    >
                                        <span class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow dark:bg-gray-800 dark:text-gray-200">
                                            Memuat peta...
                                        </span>
                                    </div>

                                    <!-- Center Pin Overlay -->
                                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center z-20">
                                        <div class="relative -mt-5">
                                            <svg class="size-12 text-red-500 drop-shadow-lg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Direction Controls -->
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-30">
                                        <div class="relative w-full h-full">
                                            <!-- Up -->
                                            <button
                                                type="button"
                                                @click="moveEditMapCenter(MAP_PAN_STEP, 0)"
                                                class="pointer-events-auto absolute top-4 left-1/2 -translate-x-1/2 rounded-lg bg-white p-3 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                                            >
                                                <svg class="size-5 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                                </svg>
                                            </button>
                                            <!-- Down -->
                                            <button
                                                type="button"
                                                @click="moveEditMapCenter(-MAP_PAN_STEP, 0)"
                                                class="pointer-events-auto absolute bottom-4 left-1/2 -translate-x-1/2 rounded-lg bg-white p-3 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                                            >
                                                <svg class="size-5 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                            <!-- Left -->
                                            <button
                                                type="button"
                                                @click="moveEditMapCenter(0, -MAP_PAN_STEP)"
                                                class="pointer-events-auto absolute top-1/2 left-4 -translate-y-1/2 rounded-lg bg-white p-3 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                                            >
                                                <svg class="size-5 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                                </svg>
                                            </button>
                                            <!-- Right -->
                                            <button
                                                type="button"
                                                @click="moveEditMapCenter(0, MAP_PAN_STEP)"
                                                class="pointer-events-auto absolute top-1/2 right-4 -translate-y-1/2 rounded-lg bg-white p-3 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                                            >
                                                <svg class="size-5 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instructions & Button -->
                                <div class="p-6 space-y-4">
                                    <!-- Use Current Location Button -->
                                    <button
                                        type="button"
                                        @click="useCurrentLocationForEdit"
                                        class="flex w-full items-center justify-center gap-3 rounded-lg border-2 border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                    >
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <span>Gunakan Lokasi Saat Ini</span>
                                    </button>

                                    <div class="rounded-lg bg-amber-50 p-4 dark:bg-amber-900/20">
                                        <div class="flex gap-3">
                                            <svg class="size-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                            </svg>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-amber-900 dark:text-amber-200">
                                                    Geser peta untuk menyesuaikan titik lokasi
                                                </p>
                                                <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                                    Pin merah di tengah menunjukkan lokasi yang akan disimpan. Geser peta hingga pin tepat di lokasi yang diinginkan.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="saveEditLocation"
                                        class="w-full rounded-lg bg-amber-600 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                    >
                                        Simpan Lokasi
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>


