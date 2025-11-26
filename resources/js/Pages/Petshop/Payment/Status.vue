<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const statusConfig = computed(() => {
    const status = props.order.payment?.transaction_status || props.order.payment_status;
    
    const configs = {
        pending: {
            icon: 'warning',
            title: 'Menunggu Pembayaran',
            message: 'Pesanan Anda sedang menunggu pembayaran. Silakan selesaikan pembayaran Anda.',
        },
        settlement: {
            icon: 'success',
            title: 'Pembayaran Berhasil',
            message: 'Terima kasih! Pembayaran Anda telah berhasil diproses.',
        },
        capture: {
            icon: 'success',
            title: 'Pembayaran Berhasil',
            message: 'Terima kasih! Pembayaran Anda telah berhasil diproses.',
        },
        paid: {
            icon: 'success',
            title: 'Pembayaran Berhasil',
            message: 'Terima kasih! Pembayaran Anda telah berhasil diproses.',
        },
        processing: {
            icon: 'success',
            title: 'Pembayaran Berhasil',
            message: 'Terima kasih! Pembayaran Anda telah berhasil diproses.',
        },
        deny: {
            icon: 'error',
            title: 'Pembayaran Ditolak',
            message: 'Maaf, pembayaran Anda ditolak. Silakan coba metode pembayaran lain.',
        },
        cancel: {
            icon: 'info',
            title: 'Pembayaran Dibatalkan',
            message: 'Pembayaran Anda telah dibatalkan.',
        },
        expire: {
            icon: 'warning',
            title: 'Pembayaran Kadaluarsa',
            message: 'Waktu pembayaran telah habis. Silakan buat pesanan baru.',
        },
    };
    
    return configs[status] || configs.pending;
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value ?? 0);
};

onMounted(() => {
    // Show SweetAlert2 based on payment status
    Swal.fire({
        icon: statusConfig.value.icon,
        title: statusConfig.value.title,
        text: statusConfig.value.message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#F59E0B',
        allowOutsideClick: false,
    });
});
</script>

<template>
    <Head title="Status Pembayaran" />

    <PublicLayout>
        <section class="bg-gray-50 py-12 dark:bg-gray-900 min-h-[70vh] flex items-center">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 w-full">
                <!-- Order Info Card -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-gray-200 p-8 shadow-lg">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
                        Informasi Pesanan
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-gray-600 dark:text-gray-400">Nomor Pesanan:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ order.order_number }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-gray-600 dark:text-gray-400">Total Pembayaran:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(order.total) }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-3 border-b border-gray-200">
                            <span class="text-gray-600 dark:text-gray-400">Status Pesanan:</span>
                            <span 
                                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                :class="{
                                    'bg-amber-100 text-amber-800': order.status === 'pending',
                                    'bg-blue-100 text-blue-800': order.status === 'processing',
                                    'bg-green-100 text-green-800': order.status === 'delivered',
                                }"
                            >
                                {{ order.status }}
                            </span>
                        </div>

                        <!-- VA Number if available -->
                        <div v-if="order.payment?.va_number" class="pt-3 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Virtual Account:</span>
                                <span class="font-mono font-bold text-lg text-gray-900 dark:text-white">
                                    {{ order.payment.va_number }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 text-right">
                                {{ order.payment.payment_type }}
                            </p>
                        </div>

                        <!-- Snap Redirect URL if pending -->
                        <div v-if="order.payment?.snap_redirect_url && order.payment?.transaction_status === 'pending'" class="pt-3">
                            <a 
                                :href="order.payment.snap_redirect_url" 
                                target="_blank"
                                class="inline-flex w-full justify-center rounded-lg bg-amber-600 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-700 transition-colors"
                            >
                                Lanjutkan Pembayaran
                            </a>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center mt-8 pt-6 border-t border-gray-200">
                        <Link :href="route('petshop.index')">
                            <PrimaryButton class="w-full sm:w-auto">
                                Lanjut Belanja
                            </PrimaryButton>
                        </Link>

                        <Link :href="route('profile.transactions.index')">
                            <SecondaryButton class="w-full sm:w-auto">
                                Lihat Pesanan Saya
                            </SecondaryButton>
                        </Link>
                    </div>
                </div>

                <!-- Help Text -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Butuh bantuan? Hubungi kami di 
                        <a href="mailto:support@klinkhewan.com" class="text-amber-600 hover:text-amber-700 font-semibold">
                            support@klinkhewan.com
                        </a>
                    </p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
