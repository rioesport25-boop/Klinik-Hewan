<script setup>
import QuantitySelector from '@/Components/Petshop/QuantitySelector.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    cart: {
        type: Object,
        default: () => ({
            items: [],
            subtotal: 0,
            total_items: 0,
        }),
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const cartItems = computed(() => props.cart.items ?? []);
const subtotal = computed(() => props.cart.subtotal ?? 0);
const isEmpty = computed(() => cartItems.value.length === 0);

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
    if (confirm('Hapus produk ini dari keranjang?')) {
        router.delete(route('petshop.cart.items.destroy', item.id), {
            preserveScroll: true,
        });
    }
};

const clearCart = () => {
    if (confirm('Kosongkan seluruh keranjang?')) {
        router.post(route('petshop.cart.clear'), {}, {
            preserveScroll: true,
        });
    }
};

const proceedToCheckout = () => {
    router.visit(route('petshop.checkout.index'));
};
</script>

<template>
    <Head title="Keranjang Belanja" />

    <PublicLayout>
        <section class="bg-gray-50 py-12 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-300">
                            Langkah 1 dari 2
                        </p>
                        <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                            Keranjang Belanja
                        </h1>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                            Periksa kembali produk sebelum melanjutkan ke proses checkout.
                        </p>
                    </div>
                    <SecondaryButton v-if="!isEmpty" @click="clearCart">
                        Kosongkan Keranjang
                    </SecondaryButton>
                </div>

                <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                    <div>
                        <div v-if="flash.success" class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 shadow-sm dark:border-green-700/40 dark:bg-green-900/40 dark:text-green-200">
                            {{ flash.success }}
                        </div>
                        <div v-if="flash.error" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm dark:border-red-700/40 dark:bg-red-900/40 dark:text-red-200">
                            {{ flash.error }}
                        </div>

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
                            <article
                                v-for="item in cartItems"
                                :key="item.id"
                                class="flex flex-col gap-4 rounded-3xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-amber-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 md:flex-row md:items-center"
                            >
                                <div class="h-28 w-28 flex-shrink-0 overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-700">
                                    <img
                                        :src="item.product?.primary_image_url || 'https://ui-avatars.com/api/?name=Petshop&background=EBF4FF&color=7F9CF5'"
                                        :alt="item.product?.name"
                                        class="h-full w-full object-cover"
                                    >
                                </div>
                                <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <Link
                                            :href="route('petshop.product.show', item.product?.slug)"
                                            class="text-lg font-semibold text-gray-900 transition hover:text-amber-600 dark:text-white dark:hover:text-amber-300"
                                        >
                                            {{ item.product?.name }}
                                        </Link>
                                        <p v-if="item.variant?.name" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Varian: {{ item.variant.name }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Harga satuan: {{ formatCurrency(item.price) }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-3">
                                        <QuantitySelector
                                            :model-value="item.quantity"
                                            :max="itemMaxStock(item)"
                                            @update:model-value="(value) => updateQuantity(item, value)"
                                        />
                                        <div class="text-right">
                                            <p class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">Subtotal</p>
                                            <p class="text-lg font-semibold text-amber-600 dark:text-amber-300">
                                                {{ formatCurrency(item.subtotal) }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            @click="removeItem(item)"
                                            class="text-xs font-semibold uppercase tracking-wide text-red-500 transition hover:text-red-600 dark:text-red-300 dark:hover:text-red-200"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>

                    <aside class="space-y-6 rounded-3xl border border-gray-200 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Ringkasan Pesanan
                        </h2>
                        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex items-center justify-between">
                                <span>Subtotal ({{ cart.total_items }} produk)</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(subtotal) }}
                                </span>
                            </div>
                            <div class="flex items-start justify-between">
                                <span>Estimasi Ongkir</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Dihitung di halaman checkout</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-dashed border-gray-200 pt-3 text-base font-semibold text-gray-900 dark:border-gray-700 dark:text-white">
                                <span>Total Sementara</span>
                                <span>{{ formatCurrency(subtotal) }}</span>
                            </div>
                        </div>

                        <PrimaryButton
                            class="w-full justify-center rounded-2xl py-3 text-base"
                            :disabled="isEmpty"
                            @click="proceedToCheckout"
                        >
                            Lanjut ke Checkout
                        </PrimaryButton>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Pastikan alamat pengiriman telah benar. Metode pembayaran Tripay akan dipilih pada langkah selanjutnya.
                        </p>
                    </aside>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
