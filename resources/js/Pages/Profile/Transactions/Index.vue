<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const showDetailModal = ref(false);
const selectedOrder = ref(null);
const loadingDetail = ref(false);

// Local filters
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const dateFilter = ref(props.filters.date || '');

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'pending', label: 'Menunggu Pembayaran' },
    { value: 'paid', label: 'Dibayar' },
    { value: 'processing', label: 'Diproses' },
    { value: 'shipped', label: 'Dikirim' },
    { value: 'delivered', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

const applyFilters = () => {
    router.get(route('profile.transactions.index'), {
        search: searchQuery.value,
        status: statusFilter.value,
        date: dateFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    dateFilter.value = '';
    applyFilters();
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'text-yellow-600 bg-yellow-50',
        paid: 'text-amber-600 bg-amber-50',
        processing: 'text-purple-600 bg-purple-50',
        shipped: 'text-indigo-600 bg-indigo-50',
        delivered: 'text-green-600 bg-green-50',
        cancelled: 'text-red-600 bg-red-50',
    };
    return colors[status] || 'text-gray-600 bg-gray-50';
};

const showOrderDetail = async (order) => {
    loadingDetail.value = true;
    showDetailModal.value = true;
    
    try {
        const response = await axios.get(route('profile.transactions.show', order.id));
        selectedOrder.value = response.data.order;
    } catch (error) {
        console.error('Error fetching order detail:', error);
        showDetailModal.value = false;
    } finally {
        loadingDetail.value = false;
    }
};

const closeModal = () => {
    showDetailModal.value = false;
    selectedOrder.value = null;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const handleBuyAgain = () => {
    if (selectedOrder.value && selectedOrder.value.items && selectedOrder.value.items.length > 0) {
        const firstProduct = selectedOrder.value.items[0];
        if (firstProduct.product_slug) {
            closeModal();
            router.visit(route('petshop.product.show', firstProduct.product_slug));
            return;
        }
    }
    // Fallback to petshop if no product found
    closeModal();
    router.visit(route('petshop.index'));
};

const handleBackToCart = () => {
    closeModal();
    router.visit(route('petshop.index'));
};

// Parse shipping address if it's JSON string
const getShippingAddress = (address) => {
    if (!address) return '';
    
    // Check if it's a JSON string
    if (typeof address === 'string' && address.trim().startsWith('{')) {
        try {
            const parsed = JSON.parse(address);
            return parsed.full_address || parsed.address || address;
        } catch (e) {
            return address;
        }
    }
    
    return address;
};
</script>

<template>
    <AppLayout title="Daftar Transaksi">
        <Head title="Daftar Transaksi" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Daftar Transaksi
                    </h1>
                </div>

                <!-- Filters -->
                <div class="mb-6 space-y-4">
                    <!-- Search -->
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari Transaksimu Disini"
                            class="w-full rounded-lg border-gray-300 py-3 ps-11 pe-4 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            @keyup.enter="applyFilters"
                        />
                        <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
                            <svg class="size-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Status and Date Filters -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <select
                            v-model="statusFilter"
                            class="w-full rounded-lg border-gray-300 py-3 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            @change="applyFilters"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>

                        <input
                            v-model="dateFilter"
                            type="date"
                            placeholder="Semua Tanggal"
                            class="w-full rounded-lg border-gray-300 py-3 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <!-- Orders List -->
                <div v-if="orders.data.length > 0" class="space-y-4">
                    <button
                        v-for="order in orders.data"
                        :key="order.id"
                        type="button"
                        @click="showOrderDetail(order)"
                        class="w-full rounded-lg bg-white p-6 text-left shadow transition hover:shadow-lg dark:bg-gray-800"
                    >
                        <div class="mb-4 flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                    <svg class="size-6 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        Grocery
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ order.created_at }}
                                    </p>
                                </div>
                            </div>
                            <span
                                :class="getStatusColor(order.status)"
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                            >
                                {{ order.status_label }}
                            </span>
                        </div>

                        <div class="mb-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ order.shipping_service }}
                                </span>
                                <svg class="size-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                No. Transaksi - {{ order.order_number }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <img
                                v-if="order.first_product.image"
                                :src="order.first_product.image"
                                :alt="order.first_product.name"
                                class="size-16 rounded-lg object-cover"
                            />
                            <div v-else class="flex size-16 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                <svg class="size-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate font-medium text-gray-900 dark:text-white">
                                    {{ order.first_product.name }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                            <div class="text-right">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(order.total) }}
                                </p>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-lg bg-white p-12 text-center shadow dark:bg-gray-800">
                    <svg class="mx-auto size-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <p class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                        Belum ada transaksi
                    </p>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Transaksi Anda akan muncul di sini setelah checkout
                    </p>
                    <Link
                        :href="route('petshop.index')"
                        class="mt-6 inline-flex items-center gap-2 rounded-lg bg-amber-500 px-6 py-3 font-semibold text-white transition hover:bg-amber-600"
                    >
                        Mulai Belanja
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="orders.data.length > 0 && (orders.prev_page_url || orders.next_page_url)" class="mt-6 flex justify-center gap-2">
                    <Link
                        v-if="orders.prev_page_url"
                        :href="orders.prev_page_url"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="orders.next_page_url"
                        :href="orders.next_page_url"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
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
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                    @click.self="closeModal"
                >
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="showDetailModal"
                            class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-gray-800"
                        >
                            <!-- Close Button -->
                            <button
                                type="button"
                                @click="closeModal"
                                class="absolute right-4 top-4 rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                            >
                                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <!-- Loading State -->
                            <div v-if="loadingDetail" class="flex items-center justify-center p-12">
                                <div class="size-12 animate-spin rounded-full border-4 border-amber-500 border-t-transparent"></div>
                            </div>

                            <!-- Modal Content -->
                            <div v-else-if="selectedOrder" class="p-6">
                                <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                                    Detail Transaksi
                                </h2>

                                <!-- Order Info -->
                                <div class="mb-6 space-y-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">No. Transaksi</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ selectedOrder.order_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Waktu</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ selectedOrder.created_at }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Status</span>
                                        <span
                                            :class="getStatusColor(selectedOrder.status)"
                                            class="rounded-full px-3 py-1 text-xs font-semibold"
                                        >
                                            {{ selectedOrder.status_label }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Store Info -->
                                <div class="mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                            <svg class="size-6 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">Grocery</h3>
                                    </div>
                                </div>

                                <!-- Shipping Service -->
                                <div class="mb-6">
                                    <h4 class="mb-2 font-semibold text-gray-900 dark:text-white">
                                        {{ selectedOrder.shipping_service }}
                                    </h4>
                                </div>

                                <!-- Products -->
                                <div class="mb-6 space-y-4">
                                    <div
                                        v-for="item in selectedOrder.items"
                                        :key="item.id"
                                        class="flex gap-4"
                                    >
                                        <img
                                            v-if="item.image"
                                            :src="item.image"
                                            :alt="item.product_name"
                                            class="size-16 rounded-lg object-cover"
                                        />
                                        <div v-else class="flex size-16 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                            <svg class="size-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">
                                                {{ item.product_name }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ item.quantity }} pcs x {{ formatCurrency(item.price) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Details -->
                                <div class="mb-6">
                                    <h4 class="mb-4 font-semibold text-gray-900 dark:text-white">
                                        Detail Pengiriman
                                    </h4>
                                    <div class="space-y-3 text-sm">
                                        <div>
                                            <p class="mb-1 font-semibold text-gray-900 dark:text-white">Penerima</p>
                                            <p class="text-gray-600 dark:text-gray-400">{{ selectedOrder.customer_name }} - {{ selectedOrder.customer_phone }}</p>
                                        </div>
                                        <div>
                                            <p class="mb-2 font-semibold text-gray-900 dark:text-white">Alamat</p>
                                            <div class="flex items-start gap-2">
                                                <svg class="size-5 mt-0.5 flex-shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                                </svg>
                                                <div class="flex-1">
                                                    <p class="font-medium text-gray-900 dark:text-white">Rumah</p>
                                                    <p class="mt-1 leading-relaxed text-gray-600 dark:text-gray-400">
                                                        {{ getShippingAddress(selectedOrder.shipping_address) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="mb-1 font-semibold text-gray-900 dark:text-white">Metode Pengiriman</p>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Instan ({{ formatCurrency(selectedOrder.shipping_cost) }})</span><br>
                                                <span class="text-xs">1 jam sampai setelah lunas</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shopping Details -->
                                <div class="mb-6">
                                    <h4 class="mb-4 font-semibold text-gray-900 dark:text-white">
                                        Rincian Belanja
                                    </h4>
                                    <div class="space-y-3 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Metode Pembayaran</span>
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                {{ selectedOrder.payment?.payment_method || 'Virtual Account' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">{{ selectedOrder.shipping_service }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(selectedOrder.subtotal) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between border-t border-gray-200 pt-3 dark:border-gray-700">
                                            <span class="font-semibold text-gray-900 dark:text-white">Subtotal</span>
                                            <span class="font-semibold text-gray-900 dark:text-white">
                                                {{ formatCurrency(selectedOrder.subtotal) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Biaya Pengiriman</span>
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(selectedOrder.shipping_cost) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between border-t border-gray-200 pt-3 dark:border-gray-700">
                                            <span class="font-bold text-gray-900 dark:text-white">Total Pembayaran</span>
                                            <span class="font-bold text-gray-900 dark:text-white">
                                                {{ formatCurrency(selectedOrder.total) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between border-t border-gray-200 pt-3 dark:border-gray-700">
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                {{ selectedOrder.payment?.payment_method || 'Virtual Account' }}
                                            </span>
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(selectedOrder.total) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-3">
                                    <button
                                        type="button"
                                        @click="handleBuyAgain"
                                        class="flex-1 rounded-lg bg-amber-600 px-4 py-3 font-semibold text-white transition hover:bg-amber-700 active:scale-95"
                                    >
                                        Beli Lagi
                                    </button>
                                    <button
                                        type="button"
                                        @click="handleBackToCart"
                                        class="flex-1 rounded-lg border-2 border-amber-600 px-4 py-3 font-semibold text-amber-600 transition hover:bg-amber-50 dark:border-amber-500 dark:text-amber-500 dark:hover:bg-amber-900/20 active:scale-95"
                                    >
                                        Kembali ke Petshop
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

