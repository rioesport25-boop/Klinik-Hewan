<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    addresses: {
        type: Array,
        default: () => [],
    },
});

const showAddressModal = ref(false);
const showMapModal = ref(false);
const searchQuery = ref('');
const currentLocation = ref(null);
const selectedAddress = ref({
    name: '',
    latitude: null,
    longitude: null,
    full_address: '',
    label: 'Rumah',
});

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
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                currentLocation.value = {
                    lat: lat,
                    lng: lng
                };
                
                // Get address from coordinates using reverse geocoding
                try {
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
                    );
                    const data = await response.json();
                    
                    selectedAddress.value.latitude = lat;
                    selectedAddress.value.longitude = lng;
                    selectedAddress.value.name = data.display_name || 'Lokasi Saat Ini';
                    selectedAddress.value.full_address = data.display_name || '';
                } catch (error) {
                    console.error('Error fetching address:', error);
                    selectedAddress.value.name = 'Lokasi Saat Ini';
                    selectedAddress.value.latitude = lat;
                    selectedAddress.value.longitude = lng;
                }
                
                // Close search modal and open map modal
                showAddressModal.value = false;
                showMapModal.value = true;
            },
            (error) => {
                console.error('Error getting location:', error);
                alert('Tidak dapat mengakses lokasi. Pastikan Anda mengizinkan akses lokasi.');
            }
        );
    } else {
        alert('Browser Anda tidak mendukung geolocation.');
    }
};

const searchLocation = () => {
    if (searchQuery.value.trim()) {
        console.log('Searching for:', searchQuery.value);
        // TODO: Implement location search functionality
    }
};

const saveAddress = () => {
    console.log('Saving address:', selectedAddress.value);
    // TODO: Send to backend to save address
    alert('Alamat berhasil disimpan! (Feature coming soon)');
    closeMapModal();
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
                            <div>
                                <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                                    Daftar Alamat Pengiriman
                                </h1>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Kelola alamat pengiriman Anda untuk checkout yang lebih cepat
                                </p>
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
                            <!-- Address cards will be displayed here -->
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
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                            <svg class="size-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                            </svg>
                                        </div>
                                        <input
                                            v-model="searchQuery"
                                            type="text"
                                            placeholder="Cari lokasi/gedung/nama jalan"
                                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 py-3 pl-12 pr-4 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                            @keyup.enter="searchLocation"
                                        >
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
                                <div class="relative h-80 w-full bg-gray-200 dark:bg-gray-700">
                                    <iframe
                                        v-if="currentLocation"
                                        :src="`https://www.openstreetmap.org/export/embed.html?bbox=${currentLocation.lng-0.01},${currentLocation.lat-0.01},${currentLocation.lng+0.01},${currentLocation.lat+0.01}&layer=mapnik&marker=${currentLocation.lat},${currentLocation.lng}`"
                                        class="h-full w-full"
                                        frameborder="0"
                                    ></iframe>
                                    
                                    <!-- Search Area Button -->
                                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                        >
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                            </svg>
                                            Cari Ulang Area
                                        </button>
                                    </div>

                                    <!-- Recenter Button -->
                                    <button
                                        type="button"
                                        class="absolute bottom-4 right-4 rounded-lg bg-white p-2 shadow-lg hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700"
                                    >
                                        <svg class="size-5 text-gray-700 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Address Details Form -->
                                <div class="p-6 space-y-4">
                                    <!-- Location Name -->
                                    <div class="flex items-start gap-3">
                                        <div class="rounded-lg bg-red-50 p-2 dark:bg-red-900/20">
                                            <svg class="size-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ selectedAddress.name || 'Lokasi Terpilih' }}
                                            </h4>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
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

                                    <!-- Save Button -->
                                    <button
                                        type="button"
                                        @click="saveAddress"
                                        class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
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
    </AppLayout>
</template>
