<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const page = usePage();

const props = defineProps({
    snap_token: {
        type: String,
        required: true,
    },
    order: {
        type: Object,
        required: true,
    },
});

onMounted(() => {
    console.log('Payment Process Page Mounted');
    console.log('Snap Token:', props.snap_token);
    console.log('Order:', props.order);

    // Load Midtrans Snap script
    const script = document.createElement('script');
    script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', 'Mid-client-38gsJLbWKKi6LH3s');
    
    script.onload = () => {
        console.log('✅ Snap script loaded, opening payment popup...');
        
        // Open Snap popup immediately
        if (window.snap && props.snap_token) {
            window.snap.pay(props.snap_token, {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    window.location.href = route('petshop.payment.finish', { 
                        order_id: props.order.order_number 
                    });
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    window.location.href = route('petshop.payment.finish', { 
                        order_id: props.order.order_number 
                    });
                },
                onError: function(result) {
                    console.log('Payment error:', result);
                    window.location.href = route('petshop.payment.error', { 
                        order_id: props.order.order_number 
                    });
                },
                onClose: function() {
                    console.log('Payment popup closed');
                    window.location.href = route('petshop.payment.unfinish', { 
                        order_id: props.order.order_number 
                    });
                }
            });
        } else {
            console.error('Snap or token not available');
        }
    };

    script.onerror = () => {
        console.error('❌ Failed to load Snap script');
        page.props.flash = { error: 'Gagal memuat sistem pembayaran. Silakan refresh halaman.' };
    };

    document.head.appendChild(script);
});
</script>

<template>
    <Head title="Memproses Pembayaran" />

    <PublicLayout>
        <section class="bg-gray-50 dark:bg-gray-900 min-h-[70vh] flex items-center justify-center">
            <div class="mx-auto max-w-md px-4 sm:px-6 lg:px-8 text-center">
                <!-- Loading Animation -->
                <div class="mb-8">
                    <div class="inline-block">
                        <svg class="animate-spin h-16 w-16 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Text -->
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    Memproses Pembayaran
                </h1>
                
                <p class="text-gray-600 dark:text-gray-400 mb-2">
                    Mohon tunggu sebentar...
                </p>
                
                <p class="text-sm text-gray-500 dark:text-gray-500">
                    Jendela pembayaran akan terbuka otomatis
                </p>

                <!-- Order Info -->
                <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg p-6 text-left">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                        Informasi Pesanan
                    </h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Nomor Pesanan:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ order.order_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total:</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(order.total) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
