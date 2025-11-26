<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch, onMounted, ref } from 'vue';

const page = usePage();

const props = defineProps({
    cart: {
        type: Object,
        required: true,
    },
    customer: {
        type: Object,
        default: () => ({}),
    },
    defaultAddress: {
        type: Object,
        default: null,
    },
    shippingMethods: {
        type: Array,
        default: () => [],
    },
});

const flash = computed(() => page.props.flash || {});

// Log flash data for debugging
watch(() => page.props.flash, (newFlash) => {
    console.log('Flash data updated:', newFlash);
}, { immediate: true, deep: true });

const form = useForm({
    customer_name: props.defaultAddress?.recipient_name || props.customer.name || '',
    customer_email: props.customer.email || '',
    customer_phone: props.defaultAddress?.phone_number || props.customer.phone || '',
    shipping_address: props.defaultAddress?.full_address || '',
    shipping_city: props.defaultAddress?.city || '',
    shipping_province: props.defaultAddress?.province || '',
    shipping_postal_code: props.defaultAddress?.postal_code || '',
    shipping_method: props.shippingMethods[0]?.code || '',
    notes: '',
});

const cartItems = computed(() => props.cart.items ?? []);
const subtotal = computed(() => props.cart.subtotal ?? 0);

const selectedShipping = computed(() =>
    props.shippingMethods.find((method) => method.code === form.shipping_method) || props.shippingMethods[0] || { fee: 0 },
);

const shippingFee = computed(() => selectedShipping.value?.fee ?? 0);
const grandTotal = computed(() => subtotal.value + shippingFee.value);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value ?? 0);
};

// Load Midtrans Snap script
const snapLoaded = ref(false);

onMounted(() => {
    console.log('Mounting checkout page...');
    console.log('Client Key:', import.meta.env.VITE_MIDTRANS_CLIENT_KEY);
    console.log('Is Production:', import.meta.env.VITE_MIDTRANS_IS_PRODUCTION);
    
    const script = document.createElement('script');
    script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', 'Mid-client-38gsJLbWKKi6LH3s');
    script.onload = () => {
        snapLoaded.value = true;
        console.log('✅ Midtrans Snap script loaded successfully');
        console.log('window.snap available:', !!window.snap);
        // Check if there's a snap token on mount
        checkAndOpenSnap();
    };
    script.onerror = () => {
        console.error('❌ Failed to load Midtrans Snap script');
    };
    document.head.appendChild(script);
});

// Function to check and open Snap
const checkAndOpenSnap = () => {
    const snapToken = flash.value.snap_token;
    const orderNumber = flash.value.order_number;
    
    if (snapToken && window.snap && snapLoaded.value) {
        console.log('Opening Snap with token:', snapToken);
        
        window.snap.pay(snapToken, {
            onSuccess: function(result) {
                console.log('Payment success:', result);
                window.location.href = route('petshop.payment.finish', { order_id: orderNumber });
            },
            onPending: function(result) {
                console.log('Payment pending:', result);
                window.location.href = route('petshop.payment.finish', { order_id: orderNumber });
            },
            onError: function(result) {
                console.log('Payment error:', result);
                window.location.href = route('petshop.payment.error', { order_id: orderNumber });
            },
            onClose: function() {
                console.log('Payment popup closed');
                window.location.href = route('petshop.payment.unfinish', { order_id: orderNumber });
            }
        });
    }
};

// Watch for snap_token in flash messages
watch(() => flash.value.snap_token, (snapToken) => {
    if (snapToken) {
        console.log('Snap token detected:', snapToken);
        // Wait a bit for Snap to be ready
        setTimeout(() => {
            checkAndOpenSnap();
        }, 500);
    }
});

const submitCheckout = () => {
    form.post(route('petshop.checkout.store'), {
        preserveScroll: false,
        onSuccess: (page) => {
            console.log('Checkout submitted successfully');
            console.log('Page props:', page.props);
            
            // Get snap token from response
            const snapToken = page.props.flash?.snap_token;
            const orderNumber = page.props.flash?.order_number;
            
            console.log('Snap Token from response:', snapToken);
            console.log('Order Number:', orderNumber);
            
            if (snapToken) {
                // Wait for Snap to be ready
                const checkSnap = setInterval(() => {
                    if (window.snap) {
                        clearInterval(checkSnap);
                        console.log('Opening Snap popup...');
                        
                        window.snap.pay(snapToken, {
                            onSuccess: function(result) {
                                console.log('Payment success:', result);
                                window.location.href = route('petshop.payment.finish', { order_id: orderNumber });
                            },
                            onPending: function(result) {
                                console.log('Payment pending:', result);
                                window.location.href = route('petshop.payment.finish', { order_id: orderNumber });
                            },
                            onError: function(result) {
                                console.log('Payment error:', result);
                                window.location.href = route('petshop.payment.error', { order_id: orderNumber });
                            },
                            onClose: function() {
                                console.log('Payment popup closed');
                                window.location.href = route('petshop.payment.unfinish', { order_id: orderNumber });
                            }
                        });
                    }
                }, 100);
                
                // Timeout after 5 seconds
                setTimeout(() => {
                    clearInterval(checkSnap);
                    if (!window.snap) {
                        console.error('Snap not loaded after 5 seconds');
                        page.props.flash = { error: 'Gagal memuat pembayaran. Silakan refresh halaman.' };
                    }
                }, 5000);
            } else {
                console.error('No snap token in response');
            }
        },
        onError: (errors) => {
            console.error('Checkout errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Checkout" />

    <PublicLayout>
        <section class="bg-white py-12 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-300">
                            Langkah 2 dari 2
                        </p>
                        <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                            Informasi Checkout
                        </h1>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                            Isi data pengiriman dengan lengkap. Pastikan informasi sudah benar sebelum konfirmasi.
                        </p>
                    </div>
                    <Link
                        :href="route('petshop.cart.show')"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-amber-600 transition hover:text-amber-700 dark:text-amber-300 dark:hover:text-amber-200"
                    >
                        &larr; Kembali ke Keranjang
                    </Link>
                </div>

                <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                    <form @submit.prevent="submitCheckout" class="space-y-8">
                        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Data Pelanggan
                            </h2>
                            <div class="mt-6 grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="customer_name" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nama Lengkap</label>
                                    <input
                                        id="customer_name"
                                        v-model="form.customer_name"
                                        type="text"
                                        autocomplete="name"
                                        class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                    >
                                    <InputError :message="form.errors.customer_name" class="mt-2" />
                                </div>
                                <div>
                                    <label for="customer_email" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Email</label>
                                    <input
                                        id="customer_email"
                                        v-model="form.customer_email"
                                        type="email"
                                        autocomplete="email"
                                        class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                    >
                                    <InputError :message="form.errors.customer_email" class="mt-2" />
                                </div>
                                <div>
                                    <label for="customer_phone" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nomor HP/WhatsApp</label>
                                    <input
                                        id="customer_phone"
                                        v-model="form.customer_phone"
                                        type="tel"
                                        autocomplete="tel"
                                        class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                    >
                                    <InputError :message="form.errors.customer_phone" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Alamat Pengiriman
                            </h2>
                            <div class="mt-6 grid gap-5">
                                <div>
                                    <label for="shipping_address" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Alamat Lengkap</label>
                                    <textarea
                                        id="shipping_address"
                                        v-model="form.shipping_address"
                                        rows="3"
                                        class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                    />
                                    <InputError :message="form.errors.shipping_address" class="mt-2" />
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="shipping_city" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kota / Kabupaten</label>
                                        <input
                                            id="shipping_city"
                                            v-model="form.shipping_city"
                                            type="text"
                                            class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                        >
                                        <InputError :message="form.errors.shipping_city" class="mt-2" />
                                    </div>
                                    <div>
                                        <label for="shipping_province" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Provinsi</label>
                                        <input
                                            id="shipping_province"
                                            v-model="form.shipping_province"
                                            type="text"
                                            class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                        >
                                        <InputError :message="form.errors.shipping_province" class="mt-2" />
                                    </div>
                                </div>

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="shipping_postal_code" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kode Pos</label>
                                        <input
                                            id="shipping_postal_code"
                                            v-model="form.shipping_postal_code"
                                            type="text"
                                            class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                        >
                                        <InputError :message="form.errors.shipping_postal_code" class="mt-2" />
                                    </div>
                                    <div>
                                        <label for="notes" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Catatan untuk Admin (Opsional)</label>
                                        <textarea
                                            id="notes"
                                            v-model="form.notes"
                                            rows="2"
                                            class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                        />
                                        <InputError :message="form.errors.notes" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <SecondaryButton type="button" @click="form.reset()">
                                Reset Form
                            </SecondaryButton>
                            <PrimaryButton
                                type="submit"
                                class="rounded-2xl px-6 py-3 text-base"
                                :disabled="form.processing"
                            >
                                Konfirmasi &amp; Lanjutkan Pembayaran
                            </PrimaryButton>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Setelah konfirmasi, Anda akan diarahkan ke halaman pembayaran Midtrans. Silakan pilih metode pembayaran dan selesaikan transaksi sebelum batas waktu yang ditentukan.
                        </p>
                    </form>

                    <aside class="space-y-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Ringkasan Pesanan
                        </h2>
                        <div class="space-y-4">
                            <article
                                v-for="item in cartItems"
                                :key="item.id"
                                class="flex items-start gap-4 rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50"
                            >
                                <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700">
                                    <img
                                        :src="item.product?.primary_image_url || 'https://ui-avatars.com/api/?name=Petshop&background=EBF4FF&color=7F9CF5'"
                                        :alt="item.product?.name"
                                        class="h-full w-full object-cover"
                                    >
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ item.product?.name }}
                                    </p>
                                    <p v-if="item.variant?.name" class="text-xs text-gray-500 dark:text-gray-400">
                                        Varian: {{ item.variant.name }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ item.quantity }} x {{ formatCurrency(item.price) }}
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-amber-600 dark:text-amber-300">
                                        {{ formatCurrency(item.subtotal) }}
                                    </p>
                                </div>
                            </article>
                        </div>

                        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex items-center justify-between">
                                <span>Subtotal Produk</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(subtotal) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Ongkir ({{ selectedShipping.label || '—' }})</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(shippingFee) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-t border-dashed border-gray-200 pt-3 text-base font-bold text-gray-900 dark:border-gray-700 dark:text-white">
                                <span>Total Pembayaran</span>
                                <span>{{ formatCurrency(grandTotal) }}</span>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Anda dapat memilih berbagai metode pembayaran yang tersedia di Midtrans (Virtual Account, E-Wallet, Credit Card, dll).
                        </p>
                    </aside>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
