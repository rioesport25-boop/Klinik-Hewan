<script setup>
import QuantitySelector from '@/Components/Petshop/QuantitySelector.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { showConfirm, showSuccess, showError } from '@/Plugins/sweetalert';

const props = defineProps({
    cart: {
        type: Object,
        default: () => ({
            items: [],
            subtotal: 0,
            total_items: 0,
        }),
    },
    defaultAddress: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const cartItems = computed(() => props.cart.items ?? []);
const subtotal = computed(() => {
    // Calculate subtotal only for selected items
    if (selectedItems.value.length === 0) {
        return 0;
    }
    return cartItems.value
        .filter(item => selectedItems.value.includes(item.id))
        .reduce((sum, item) => sum + (item.subtotal || 0), 0);
});
const isEmpty = computed(() => cartItems.value.length === 0);

// Confirm dialog state
const confirmDialog = ref({
    show: false,
    title: '',
    message: '',
    type: 'danger',
    onConfirm: null
});

const showConfirmDialog = (title, message, onConfirm, type = 'danger') => {
    confirmDialog.value = {
        show: true,
        title,
        message,
        type,
        onConfirm
    };
};

const handleConfirm = () => {
    if (confirmDialog.value.onConfirm) {
        confirmDialog.value.onConfirm();
    }
    confirmDialog.value.show = false;
};

const handleCancelDialog = () => {
    confirmDialog.value.show = false;
};

// Checkbox state for "Pilih Semua"
const selectedItems = ref([]);
const isAllSelected = computed({
    get: () => cartItems.value.length > 0 && selectedItems.value.length === cartItems.value.length,
    set: (value) => {
        if (value) {
            selectedItems.value = cartItems.value.map(item => item.id);
        } else {
            selectedItems.value = [];
        }
    }
});

// Toggle individual item selection
const toggleItemSelection = (itemId) => {
    const index = selectedItems.value.indexOf(itemId);
    if (index > -1) {
        selectedItems.value.splice(index, 1);
    } else {
        selectedItems.value.push(itemId);
    }
};

// Delete selected items
const deleteSelectedItems = () => {
    if (selectedItems.value.length === 0) {
        page.props.flash = {
            warning: 'Pilih produk yang ingin dihapus terlebih dahulu'
        };
        return;
    }
    
    showConfirmDialog(
        'Hapus Produk',
        `Apakah Anda yakin ingin menghapus ${selectedItems.value.length} produk dari keranjang?`,
        () => {
            router.post(route('petshop.cart.removeMultiple'), {
                item_ids: selectedItems.value
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    selectedItems.value = [];
                }
            });
        },
        'danger'
    );
};

// Modal state
const showDeliveryModal = ref(false);
const showTimePickerModal = ref(false);
const selectedDeliveryType = ref('delivery'); // 'delivery' or 'pickup'
const selectedDeliveryOption = ref(null); // 'instant' or 'regular' - null by default (user must select)
const selectedDeliveryTime = ref(null); // Selected time slot
const selectedDeliveryDate = ref('today'); // 'today' or 'tomorrow'
const isProcessingCheckout = ref(false);

// Calculate shipping fee based on delivery type
const shippingFee = computed(() => {
    if (selectedDeliveryType.value === 'pickup') {
        return 0; // Pickup is free
    }
    
    // Jika tipe delivery tapi belum pilih instant/regular, return null
    if (selectedDeliveryType.value === 'delivery' && !selectedDeliveryOption.value) {
        return null;
    }
    
    if (selectedDeliveryOption.value === 'instant') {
        // Free shipping for instant delivery above Rp 150.000
        if (subtotal.value >= 150000) {
            return 0;
        }
        return 7000; // Instant delivery Rp 7.000
    }
    
    // Regular delivery
    // Free shipping for regular delivery above Rp 30.000
    if (subtotal.value >= 30000) {
        return 0;
    }
    return 5000; // Regular delivery Rp 5.000
});

// Calculate total
const total = computed(() => subtotal.value + (shippingFee.value || 0));

// Check if current time is within operational hours (07:00 - 21:00 WIB)
const isOperationalHours = computed(() => {
    const now = new Date();
    const currentHour = now.getHours();
    // Operational hours: 07:00 - 20:59 (before 21:00)
    return currentHour >= 7 && currentHour < 21;
});

// Check if "Instant" delivery is available (only during operational hours)
const isInstantAvailable = computed(() => {
    return isOperationalHours.value;
});

// Check if "Hari ini" is available (only during operational hours)
const isTodayAvailable = computed(() => {
    return isOperationalHours.value;
});

// Check if "Pickup" is available (only during operational hours)
const isPickupAvailable = computed(() => {
    return isOperationalHours.value;
});

// Get date labels
const getDateLabel = (type) => {
    const today = new Date();
    const date = new Date(today);
    
    if (type === 'today') {
        return {
            label: 'Hari ini',
            date: date.getDate(),
            month: date.toLocaleDateString('id-ID', { month: 'short' })
        };
    } else {
        date.setDate(date.getDate() + 1);
        return {
            label: 'Besok',
            date: date.getDate(),
            month: date.toLocaleDateString('id-ID', { month: 'short' })
        };
    }
};

// Generate time slots starting from 1 hour ahead
const timeSlots = computed(() => {
    const slots = [];
    const now = new Date();
    
    // If today is selected and it's late, start from tomorrow
    let startHour = selectedDeliveryDate.value === 'today' ? now.getHours() + 1 : 7;
    
    // Generate time slots from startHour or 7 AM, whichever is later
    const firstSlot = Math.max(startHour, 7);
    
    // Generate slots from 07:00 to 20:00
    for (let hour = firstSlot; hour <= 20; hour++) {
        const hours = String(hour).padStart(2, '0');
        const timeString = `${hours}.00 - ${hours}.59`;
        slots.push({
            display: timeString,
            value: `${hours}:00`
        });
    }
    
    return slots;
});

const openDeliveryModal = () => {
    showDeliveryModal.value = true;
};

const closeDeliveryModal = () => {
    showDeliveryModal.value = false;
};

const openTimePickerModal = () => {
    showDeliveryModal.value = false;
    showTimePickerModal.value = true;
};

const closeTimePickerModal = () => {
    showTimePickerModal.value = false;
    showDeliveryModal.value = true;
};

const selectTimeSlot = (time) => {
    selectedDeliveryTime.value = time;
};

const selectDeliveryDate = (dateType) => {
    // Prevent selecting "today" if outside operational hours
    if (dateType === 'today' && !isTodayAvailable.value) {
        return;
    }
    selectedDeliveryDate.value = dateType;
    selectedDeliveryTime.value = null; // Reset time when date changes
};

const selectDeliveryOption = (option) => {
    // Prevent selecting "instant" if outside operational hours
    if (option === 'instant' && !isInstantAvailable.value) {
        return;
    }
    selectedDeliveryOption.value = option;
    
    // If selecting "regular", automatically open time picker modal
    if (option === 'regular') {
        showDeliveryModal.value = false;
        showTimePickerModal.value = true;
    }
};

const selectDeliveryType = (type) => {
    // Prevent selecting "pickup" if outside operational hours
    if (type === 'pickup' && !isPickupAvailable.value) {
        return;
    }
    selectedDeliveryType.value = type;
};

const confirmTimeSelection = () => {
    showTimePickerModal.value = false;
    showDeliveryModal.value = true;
};

// Auto-adjust selections when outside operational hours
watch(isOperationalHours, (newValue) => {
    if (!newValue) {
        // Outside operational hours - reset instant and pickup selections
        if (selectedDeliveryType.value === 'pickup') {
            selectedDeliveryType.value = 'delivery';
            selectedDeliveryOption.value = null; // Reset to null, user must select
        }
        if (selectedDeliveryOption.value === 'instant') {
            selectedDeliveryOption.value = null; // Reset to null, user must select
        }
        if (selectedDeliveryDate.value === 'today') {
            selectedDeliveryDate.value = 'tomorrow';
        }
    }
});

const confirmDeliveryType = () => {
    // Validate that delivery option is selected for delivery type
    if (selectedDeliveryType.value === 'delivery' && !selectedDeliveryOption.value) {
        page.props.flash = { warning: 'Silakan pilih tipe pengiriman terlebih dahulu (Instan atau Reguler).' };
        return;
    }
    
    // If regular delivery is selected but no time selected, open time picker
    if (selectedDeliveryType.value === 'delivery' && 
        selectedDeliveryOption.value === 'regular' && 
        !selectedDeliveryTime.value) {
        openTimePickerModal();
        return;
    }
    
    showDeliveryModal.value = false;
};

const getDeliveryLabel = () => {
    if (selectedDeliveryType.value === 'pickup') {
        return 'Ambil Toko';
    }
    if (selectedDeliveryOption.value === 'instant') {
        return 'Pesan Antar - Instan (Rp 7.000)';
    }
    if (selectedDeliveryOption.value === 'regular' && selectedDeliveryTime.value) {
        const dateLabel = getDateLabel(selectedDeliveryDate.value);
        return `Pesan Antar - Reguler (${dateLabel.date} ${dateLabel.month}, ${selectedDeliveryTime.value}) - Rp 5.000`;
    }
    if (selectedDeliveryOption.value === 'regular') {
        return 'Pesan Antar - Reguler (Pilih Waktu) - Rp 5.000';
    }
    return 'Pilih Tipe Pengiriman';
};

const processCheckout = () => {
    // Validate that at least one item is selected
    if (selectedItems.value.length === 0) {
        page.props.flash = { warning: 'Silakan pilih minimal satu produk untuk checkout.' };
        return;
    }

    // Validate that delivery type option is selected for delivery
    if (selectedDeliveryType.value === 'delivery' && !selectedDeliveryOption.value) {
        page.props.flash = { warning: 'Silakan pilih tipe pengiriman terlebih dahulu (Instan atau Reguler).' };
        openDeliveryModal();
        return;
    }
    
    // Validate delivery time for regular delivery
    if (selectedDeliveryType.value === 'delivery' && 
        selectedDeliveryOption.value === 'regular' && 
        !selectedDeliveryTime.value) {
        page.props.flash = { warning: 'Silakan pilih waktu pengiriman terlebih dahulu.' };
        return;
    }

    // Check if default address exists for delivery
    if (selectedDeliveryType.value === 'delivery' && !props.defaultAddress) {
        page.props.flash = { warning: 'Silakan tambahkan alamat pengiriman terlebih dahulu di halaman profil.' };
        return;
    }

    // Validate that default address has all required fields
    if (selectedDeliveryType.value === 'delivery' && props.defaultAddress) {
        const missingFields = [];
        
        if (!props.defaultAddress.recipient_name) missingFields.push('Nama Penerima');
        if (!props.defaultAddress.phone_number) missingFields.push('Nomor Telepon');
        if (!props.defaultAddress.full_address) missingFields.push('Alamat Lengkap');
        if (!props.defaultAddress.city) missingFields.push('Kota');
        if (!props.defaultAddress.province) missingFields.push('Provinsi');
        if (!props.defaultAddress.postal_code) missingFields.push('Kode Pos');
        
        if (missingFields.length > 0) {
            page.props.flash = { warning: `Alamat pengiriman belum lengkap. Mohon lengkapi: ${missingFields.join(', ')}. Silakan perbarui alamat di halaman profil.` };
            return;
        }
    }

    isProcessingCheckout.value = true;

    router.post(route('petshop.cart.checkout'), {
        selected_items: selectedItems.value, // Send only selected item IDs
        delivery_type: selectedDeliveryType.value,
        delivery_option: selectedDeliveryOption.value,
        delivery_date: selectedDeliveryDate.value,
        delivery_time: selectedDeliveryTime.value,
        shipping_fee: shippingFee.value,
        shipping_address: selectedDeliveryType.value === 'delivery' ? props.defaultAddress : null,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            const snapToken = page.props.flash?.snap_token;
            const orderNumber = page.props.flash?.order_number;
            
            if (snapToken && window.snap) {
                // Open Midtrans payment popup
                window.snap.pay(snapToken, {
                    onSuccess: function(result) {
                        router.visit(route('petshop.payment.status', { order_number: orderNumber }));
                    },
                    onPending: function(result) {
                        router.visit(route('petshop.payment.status', { order_number: orderNumber }));
                    },
                    onError: function(result) {
                        isProcessingCheckout.value = false;
                        page.props.flash = { error: 'Pembayaran gagal. Silakan coba lagi.' };
                    },
                    onClose: function() {
                        isProcessingCheckout.value = false;
                    }
                });
            } else {
                isProcessingCheckout.value = false;
                page.props.flash = { error: 'Gagal memuat sistem pembayaran. Silakan refresh halaman.' };
            }
        },
        onError: (errors) => {
            isProcessingCheckout.value = false;
            console.error('Checkout Error:', errors);
            
            // Show more detailed error message
            const errorMessage = errors.message || Object.values(errors).flat().join(', ') || 'Terjadi kesalahan. Silakan coba lagi.';
            page.props.flash = { error: errorMessage };
        },
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value ?? 0);
};

const itemMaxStock = (item) => {
    if (item.variant?.stock !== undefined && item.variant?.stock !== null) {
        return Math.max(item.variant.stock, 1);
    }

    if (item.product?.stock !== undefined && item.product?.stock !== null) {
        return Math.max(item.product.stock, 1);
    }

    return 99;
};

const updateQuantity = (item, quantity) => {
    if (quantity < 1) {
        return;
    }

    router.patch(route('petshop.cart.items.update', item.id), {
        quantity,
    }, {
        preserveScroll: true,
    });
};

const removeItem = (item) => {
    showConfirm({
        title: 'Hapus Produk?',
        text: 'Apakah Anda yakin ingin menghapus produk ini dari keranjang?',
        icon: 'warning',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('petshop.cart.items.destroy', item.id), {
                preserveScroll: true,
                onSuccess: () => {
                    showSuccess('Produk berhasil dihapus dari keranjang');
                }
            });
        }
    });
};

const clearCart = () => {
    showConfirm({
        title: 'Kosongkan Keranjang?',
        text: 'Apakah Anda yakin ingin menghapus semua produk dari keranjang?',
        icon: 'warning',
        confirmButtonText: 'Ya, Kosongkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('petshop.cart.clear'), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    showSuccess('Keranjang berhasil dikosongkan');
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Keranjang Belanja" />

    <PublicLayout>
        <section class="bg-gray-50 py-12 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                        Keranjang Belanja
                    </h1>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                        Periksa kembali produk sebelum melanjutkan ke proses checkout.
                    </p>
                </div>

                <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                    <div>
                        <div v-if="isEmpty" class="rounded-3xl border border-dashed border-gray-300 bg-white p-12 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-gray-100 text-amber-500 dark:bg-gray-700">
                                <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437m0 0 1.35 5.068m1.65 6.187h9.75m-9.75 0a2.25 2.25 0 11-4.5 0m4.5 0a2.25 2.25 0 01-4.5 0m14.25 0a2.25 2.25 0 11-4.5 0m4.5 0a2.25 2.25 0 01-4.5 0m0 0H7.125m12.75-7.875-1.064 4.256a1.125 1.125 0 01-1.09.844H8.978a1.125 1.125 0 01-1.09-.876l-1.148-4.599M7.5 6.75h13.125" />
                                </svg>
                            </div>
                            <h2 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                                Keranjang masih kosong
                            </h2>
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                Yuk lihat-lihat produk petshop kami dan tambahkan ke keranjang.
                            </p>
                            <Link
                                :href="route('petshop.index')"
                                class="mt-6 inline-flex items-center rounded-full bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                            >
                                Belanja Sekarang
                            </Link>
                        </div>

                        <div v-else class="space-y-4">
                            <!-- Header Grid: Pilih Semua dan Hapus -->
                            <div class="flex items-center justify-between rounded-3xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input 
                                        type="checkbox" 
                                        v-model="isAllSelected"
                                        class="size-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800"
                                    >
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Semua</span>
                                </label>
                                <button
                                    v-if="selectedItems.length > 0"
                                    @click="deleteSelectedItems"
                                    class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition"
                                >
                                    Hapus
                                </button>
                            </div>

                            <!-- Cart Items -->
                            <article
                                v-for="item in cartItems"
                                :key="item.id"
                                class="flex items-center gap-4 rounded-3xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-amber-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800"
                            >
                                <!-- Checkbox -->
                                <input 
                                    type="checkbox"
                                    :checked="selectedItems.includes(item.id)"
                                    @change="toggleItemSelection(item.id)"
                                    class="size-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 flex-shrink-0"
                                >
                                
                                <!-- Product Image -->
                                <div class="h-20 w-20 md:h-28 md:w-28 flex-shrink-0 overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-700">
                                    <img
                                        :src="item.product?.primary_image_url || 'https://ui-avatars.com/api/?name=Petshop&background=EBF4FF&color=7F9CF5'"
                                        :alt="item.product?.name"
                                        class="h-full w-full object-cover"
                                    >
                                </div>
                                
                                <!-- Product Info -->
                                <div class="flex flex-1 flex-col gap-1">
                                    <Link
                                        :href="route('petshop.product.show', item.product?.slug)"
                                        class="text-base md:text-lg font-semibold text-gray-900 transition hover:text-amber-600 dark:text-white dark:hover:text-amber-300 line-clamp-2"
                                    >
                                        {{ item.product?.name }}
                                    </Link>
                                    <p v-if="item.variant?.name" class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                        Varian: {{ item.variant.name }}
                                    </p>
                                </div>

                                <!-- Delete Icon -->
                                <button
                                    type="button"
                                    @click="removeItem(item)"
                                    class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-700 dark:hover:text-red-400 flex-shrink-0"
                                    title="Hapus produk"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>

                                <!-- Quantity Selector -->
                                <div class="flex-shrink-0">
                                    <QuantitySelector
                                        :model-value="item.quantity"
                                        :max="itemMaxStock(item)"
                                        @update:model-value="(value) => updateQuantity(item, value)"
                                    />
                                </div>

                                <!-- Subtotal - Di Ujung Kanan -->
                                <div class="text-right flex-shrink-0 min-w-[100px]">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Subtotal</p>
                                    <p class="text-base md:text-lg font-bold text-amber-600 dark:text-amber-300">
                                        {{ formatCurrency(item.subtotal) }}
                                    </p>
                                </div>
                            </article>
                        </div>
                    </div>

                    <aside class="space-y-6">
                        <!-- Free Shipping Info - Dynamic based on delivery option -->
                        <div v-if="selectedDeliveryType === 'delivery' && ((selectedDeliveryOption === 'regular' && subtotal < 30000) || (selectedDeliveryOption === 'instant' && subtotal < 150000))" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-800 dark:bg-amber-900/20">
                            <div class="flex items-start gap-3">
                                <svg class="size-5 flex-shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-amber-900 dark:text-amber-200">
                                        Dapatkan gratis ongkir untuk pembelanjaan di atas <span class="font-bold">{{ selectedDeliveryOption === 'instant' ? 'Rp 150.000' : 'Rp 30.000' }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tipe Pemesanan & Alamat Pengiriman (Gabungan) -->
                        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800 space-y-6">
                            <!-- Tipe Pemesanan -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                                    Tipe Pemesanan
                                </h3>
                                <button
                                    type="button"
                                    @click="openDeliveryModal"
                                    class="w-full flex items-center gap-3 rounded-xl border-2 border-amber-200 bg-amber-50 p-3 transition hover:border-blue-300 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-900/20 dark:hover:border-blue-700 dark:hover:bg-amber-900/30"
                                >
                                    <div class="flex-shrink-0">
                                        <svg v-if="selectedDeliveryType === 'delivery'" class="size-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                        </svg>
                                        <svg v-else class="size-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 text-left">
                                        <p class="text-sm font-semibold" :class="selectedDeliveryType === 'delivery' && !selectedDeliveryOption ? 'text-red-600 dark:text-red-400' : 'text-amber-900 dark:text-amber-200'">
                                            {{ getDeliveryLabel() }}
                                            <svg v-if="selectedDeliveryOption || selectedDeliveryType === 'pickup'" class="inline-block size-4 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </p>
                                        <p v-if="selectedDeliveryType === 'delivery' && !selectedDeliveryOption" class="text-xs text-red-600 dark:text-red-400 font-medium">
                                            ⚠️ Wajib pilih tipe pengiriman
                                        </p>
                                        <p v-else-if="selectedDeliveryType === 'delivery' && selectedDeliveryOption === 'instant'" class="text-xs" :class="subtotal >= 150000 ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-amber-700 dark:text-amber-300'">
                                            {{ subtotal >= 150000 ? 'Gratis Ongkir' : 'Rp 7.000' }} - 1 jam sampai setelah lunas
                                        </p>
                                        <p v-else-if="selectedDeliveryType === 'delivery' && selectedDeliveryOption === 'regular' && selectedDeliveryTime" class="text-xs" :class="subtotal >= 30000 ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-amber-700 dark:text-amber-300'">
                                            {{ subtotal >= 30000 ? 'Gratis Ongkir' : 'Rp 5.000' }}
                                        </p>
                                        <p v-else-if="selectedDeliveryType === 'delivery' && selectedDeliveryOption === 'regular'" class="text-xs text-amber-600 dark:text-amber-400">
                                            Pilih Waktu Pengiriman
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <svg class="size-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </div>
                                </button>
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-gray-200 dark:border-gray-700"></div>

                            <!-- Alamat Pengiriman -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Alamat Pengiriman
                                    </h3>
                                    <Link
                                        v-if="$page.props.auth.user"
                                        :href="route('profile.addresses.index')"
                                        class="text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300"
                                    >
                                        Ubah Alamat
                                    </Link>
                                </div>
                                
                                <div v-if="defaultAddress" class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/50">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="size-4 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ defaultAddress.label || 'Alamat Default' }}
                                            </p>
                                            <p class="text-xs text-gray-700 dark:text-gray-300 mt-1">
                                                {{ defaultAddress.recipient_name }} ({{ defaultAddress.phone_number }})
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1.5 leading-relaxed">
                                                {{ defaultAddress.full_address }}
                                                <template v-if="defaultAddress.city || defaultAddress.province">
                                                    <br>
                                                    {{ [defaultAddress.city, defaultAddress.province, defaultAddress.postal_code].filter(Boolean).join(', ') }}
                                                </template>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-3 text-center dark:border-gray-700 dark:bg-gray-900/50">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        <template v-if="$page.props.auth.user">
                                            Belum ada alamat default.
                                        </template>
                                        <template v-else>
                                            Silakan login untuk melihat alamat pengiriman.
                                        </template>
                                    </p>
                                    <Link
                                        v-if="$page.props.auth.user"
                                        :href="route('profile.addresses.index')"
                                        class="mt-2 inline-block text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300"
                                    >
                                        Tambah Alamat
                                    </Link>
                                    <Link
                                        v-else
                                        :href="route('login')"
                                        class="mt-2 inline-block text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300"
                                    >
                                        Login
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Ringkasan Pesanan -->
                        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Ringkasan Pesanan
                            </h2>
                            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300 mt-4">
                            <div class="flex items-center justify-between">
                                <span>Subtotal ({{ selectedItems.length }} produk dipilih)</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(subtotal) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span>Ongkir</span>
                                    <span v-if="shippingFee === 0 && selectedDeliveryType === 'delivery' && selectedDeliveryOption" class="text-xs text-green-600 dark:text-green-400">
                                        🎉 Gratis ongkir!
                                    </span>
                                </div>
                                <span class="font-semibold" :class="shippingFee === 0 && shippingFee !== null ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-white'">
                                    {{ shippingFee === null ? '-' : (shippingFee === 0 ? 'Gratis' : formatCurrency(shippingFee)) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-t border-dashed border-gray-200 pt-3 text-base font-semibold text-gray-900 dark:border-gray-700 dark:text-white">
                                <span>Total</span>
                                <span>{{ formatCurrency(total) }}</span>
                            </div>
                        </div>

                        <PrimaryButton
                            class="w-full justify-center rounded-2xl py-3 text-base"
                            :disabled="isEmpty || isProcessingCheckout || selectedItems.length === 0"
                            @click="processCheckout"
                        >
                            {{ isProcessingCheckout ? 'Memproses...' : (selectedItems.length === 0 ? 'Pilih Produk Terlebih Dahulu' : 'Bayar Sekarang') }}
                        </PrimaryButton>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Klik tombol di atas untuk melanjutkan ke pembayaran Midtrans.
                            </p>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        <!-- Modal Pilih Tipe Pengiriman -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showDeliveryModal" class="fixed inset-0 z-40 overflow-y-auto">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeDeliveryModal"></div>

                    <!-- Modal Content -->
                    <div class="flex min-h-full items-center justify-center p-4">
                        <Transition
                            enter-active-class="transition ease-out duration-300"
                            enter-from-class="opacity-0 translate-y-4 scale-95"
                            enter-to-class="opacity-100 translate-y-0 scale-100"
                            leave-active-class="transition ease-in duration-200"
                            leave-from-class="opacity-100 translate-y-0 scale-100"
                            leave-to-class="opacity-0 translate-y-4 scale-95"
                        >
                            <div v-if="showDeliveryModal" class="relative w-full max-w-md transform rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
                                <!-- Modal Header -->
                                <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Pilih Tipe Pengiriman
                                    </h3>
                                    <button
                                        type="button"
                                        @click="closeDeliveryModal"
                                        class="absolute right-4 top-4 rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                    >
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Modal Body -->
                                <div class="p-6 space-y-4">
                                    <!-- Tabs -->
                                    <div class="flex gap-2">
                                        <button
                                            type="button"
                                            @click="selectDeliveryType('delivery')"
                                            :class="[
                                                'flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition',
                                                selectedDeliveryType === 'delivery'
                                                    ? 'bg-amber-600 text-white shadow-md'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                                            ]"
                                        >
                                            Pesan Antar
                                        </button>
                                        <button
                                            type="button"
                                            @click="selectDeliveryType('pickup')"
                                            :disabled="!isPickupAvailable"
                                            :class="[
                                                'flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition',
                                                !isPickupAvailable
                                                    ? 'cursor-not-allowed opacity-50 bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500'
                                                    : selectedDeliveryType === 'pickup'
                                                    ? 'bg-amber-600 text-white shadow-md'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                                            ]"
                                        >
                                            Ambil Toko
                                            <span v-if="!isPickupAvailable" class="block text-xs mt-0.5">(Tutup)</span>
                                        </button>
                                    </div>

                                    <!-- Delivery Options (only show when delivery type is selected) -->
                                    <div v-if="selectedDeliveryType === 'delivery'" class="space-y-3">
                                        <!-- Instant Delivery -->
                                        <button
                                            type="button"
                                            @click="selectDeliveryOption('instant')"
                                            :disabled="!isInstantAvailable"
                                            :class="[
                                                'w-full rounded-xl border-2 p-4 text-left transition',
                                                !isInstantAvailable
                                                    ? 'cursor-not-allowed opacity-50 border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800'
                                                    : selectedDeliveryOption === 'instant'
                                                    ? 'border-amber-500 bg-amber-50 dark:border-amber-400 dark:bg-amber-900/20'
                                                    : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600'
                                            ]"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <h4 :class="[
                                                            'font-semibold',
                                                            selectedDeliveryOption === 'instant'
                                                                ? 'text-amber-900 dark:text-amber-200'
                                                                : 'text-gray-900 dark:text-white'
                                                        ]">
                                                            Instan
                                                        </h4>
                                                        <span :class="[
                                                            'text-sm font-medium',
                                                            subtotal >= 150000 
                                                                ? 'text-green-600 dark:text-green-400'
                                                                : selectedDeliveryOption === 'instant'
                                                                ? 'text-amber-700 dark:text-amber-300'
                                                                : 'text-gray-600 dark:text-gray-400'
                                                        ]">
                                                            {{ subtotal >= 150000 ? 'Gratis' : 'Rp 7.000' }}
                                                        </span>
                                                    </div>
                                                    <p :class="[
                                                        'mt-1 text-sm',
                                                        !isInstantAvailable
                                                            ? 'text-red-600 dark:text-red-400'
                                                            : selectedDeliveryOption === 'instant'
                                                            ? 'text-amber-600 dark:text-amber-400'
                                                            : 'text-gray-500 dark:text-gray-400'
                                                    ]">
                                                        <span v-if="!isInstantAvailable">Tidak tersedia di luar jam operasional (07:00 - 21:00)</span>
                                                        <span v-else>1 jam sampai setelah lunas</span>
                                                    </p>
                                                </div>
                                                <div v-if="selectedDeliveryOption === 'instant'" class="flex-shrink-0">
                                                    <svg class="size-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </button>

                                        <!-- Regular Delivery -->
                                        <button
                                            type="button"
                                            @click="selectDeliveryOption('regular')"
                                            :class="[
                                                'w-full rounded-xl border-2 p-4 text-left transition',
                                                selectedDeliveryOption === 'regular'
                                                    ? 'border-amber-500 bg-amber-50 dark:border-amber-400 dark:bg-amber-900/20'
                                                    : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600'
                                            ]"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <h4 :class="[
                                                            'font-semibold',
                                                            selectedDeliveryOption === 'regular'
                                                                ? 'text-amber-900 dark:text-amber-200'
                                                                : 'text-gray-900 dark:text-white'
                                                        ]">
                                                            Reguler - Pilih Waktu
                                                        </h4>
                                                        <span :class="[
                                                            'text-sm font-medium',
                                                            subtotal >= 30000 
                                                                ? 'text-green-600 dark:text-green-400'
                                                                : selectedDeliveryOption === 'regular'
                                                                ? 'text-amber-600 dark:text-amber-400'
                                                                : 'text-gray-600 dark:text-gray-400'
                                                        ]">
                                                            {{ subtotal >= 30000 ? 'Gratis' : 'Rp 5.000' }}
                                                        </span>
                                                    </div>
                                                    <p :class="[
                                                        'mt-1 text-sm',
                                                        selectedDeliveryOption === 'regular'
                                                            ? 'text-amber-600 dark:text-amber-400'
                                                            : 'text-gray-500 dark:text-gray-400'
                                                    ]">
                                                        <template v-if="selectedDeliveryTime">
                                                            {{ getDateLabel(selectedDeliveryDate).date }} {{ getDateLabel(selectedDeliveryDate).month }}, {{ selectedDeliveryTime }}
                                                        </template>
                                                        <template v-else>
                                                            Pilih Waktu Pengiriman
                                                        </template>
                                                    </p>
                                                </div>
                                                <div v-if="selectedDeliveryOption === 'regular'" class="flex-shrink-0">
                                                    <svg class="size-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </button>

                                        <!-- Time Selection Button (shown when regular is selected) -->
                                        <button
                                            v-if="selectedDeliveryOption === 'regular'"
                                            type="button"
                                            @click="openTimePickerModal"
                                            class="w-full rounded-lg bg-amber-600 px-4 py-3 text-sm font-medium text-white shadow transition hover:bg-amber-700"
                                        >
                                            <div class="flex items-center justify-center gap-2">
                                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ selectedDeliveryTime ? 'Ubah Waktu Pengiriman' : 'Pilih Waktu Pengiriman' }}</span>
                                            </div>
                                        </button>
                                    </div>

                                    <!-- Pickup Message -->
                                    <div v-else class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            Pesanan Anda akan disiapkan dan dapat diambil di toko kami.
                                        </p>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="flex gap-3 border-t border-gray-200 p-6 dark:border-gray-700">
                                    <button
                                        type="button"
                                        @click="closeDeliveryModal"
                                        class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmDeliveryType"
                                        class="flex-1 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-medium text-white shadow-md transition hover:bg-amber-700"
                                    >
                                        Konfirmasi
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>

            <!-- Time Picker Modal -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showTimePickerModal"
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                    @click="closeTimePickerModal"
                >
                    <div @click.stop>
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
                            enter-to-class="translate-y-0 opacity-100 sm:scale-100"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="translate-y-0 opacity-100 sm:scale-100"
                            leave-to-class="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
                        >
                            <div v-if="showTimePickerModal" class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-800">
                                <!-- Modal Header -->
                                <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                                Pilih Waktu Pengiriman
                                            </h3>
                                        </div>
                                        <button
                                            type="button"
                                            @click="closeTimePickerModal"
                                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700"
                                        >
                                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Modal Body -->
                                <div class="max-h-96 overflow-y-auto p-6">
                                    <!-- Date Selection (Hari) -->
                                    <div class="mb-6">
                                        <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Hari</h4>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button
                                                type="button"
                                                @click="selectDeliveryDate('today')"
                                                :disabled="!isTodayAvailable"
                                                :class="[
                                                    'flex flex-col items-center rounded-xl border-2 px-4 py-3 transition',
                                                    !isTodayAvailable
                                                        ? 'cursor-not-allowed opacity-50 border-gray-200 bg-gray-100 text-gray-500 dark:border-gray-700 dark:bg-gray-800'
                                                        : selectedDeliveryDate === 'today'
                                                        ? 'border-amber-500 bg-amber-500 text-white dark:border-amber-400 dark:bg-amber-500'
                                                        : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                                ]"
                                            >
                                                <span class="text-xs font-medium">{{ getDateLabel('today').label }}</span>
                                                <span class="mt-1 text-lg font-bold">{{ getDateLabel('today').date }} {{ getDateLabel('today').month }}</span>
                                                <span v-if="!isTodayAvailable" class="mt-1 text-[10px] text-red-600 dark:text-red-400">Tutup</span>
                                            </button>
                                            <button
                                                type="button"
                                                @click="selectDeliveryDate('tomorrow')"
                                                :class="[
                                                    'flex flex-col items-center rounded-xl border-2 px-4 py-3 transition',
                                                    selectedDeliveryDate === 'tomorrow'
                                                        ? 'border-amber-500 bg-amber-500 text-white dark:border-amber-400 dark:bg-amber-500'
                                                        : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300'
                                                ]"
                                            >
                                                <span class="text-xs font-medium">{{ getDateLabel('tomorrow').label }}</span>
                                                <span class="mt-1 text-lg font-bold">{{ getDateLabel('tomorrow').date }} {{ getDateLabel('tomorrow').month }}</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Time Selection (Waktu) -->
                                    <div>
                                        <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Waktu</h4>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button
                                                v-for="timeSlot in timeSlots"
                                                :key="timeSlot.value"
                                                type="button"
                                                @click="selectTimeSlot(timeSlot.value)"
                                                :class="[
                                                    'flex items-center justify-between rounded-xl border-2 px-4 py-3 text-left transition',
                                                    selectedDeliveryTime === timeSlot.value
                                                        ? 'border-amber-500 bg-amber-50 text-amber-900 dark:border-amber-400 dark:bg-amber-900/30 dark:text-amber-200'
                                                        : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600'
                                                ]"
                                            >
                                                <span class="text-sm font-medium">{{ timeSlot.display }}</span>
                                                <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="flex gap-3 border-t border-gray-200 p-6 dark:border-gray-700">
                                    <button
                                        type="button"
                                        @click="closeTimePickerModal"
                                        class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmTimeSelection"
                                        :disabled="!selectedDeliveryTime"
                                        :class="[
                                            'flex-1 rounded-lg px-4 py-2.5 text-sm font-medium text-white shadow-md transition',
                                            selectedDeliveryTime
                                                ? 'bg-amber-600 hover:bg-amber-700'
                                                : 'cursor-not-allowed bg-gray-400 dark:bg-gray-600'
                                        ]"
                                    >
                                        Konfirmasi
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </PublicLayout>
</template>

