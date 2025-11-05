<script setup>
import Pagination from '@/Components/Pagination.vue';
import ProductCard from '@/Components/Petshop/ProductCard.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    priceRange: {
        type: Object,
        default: () => ({ min: 0, max: 0 }),
    },
    sortOptions: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const state = reactive({
    search: props.filters.search || '',
    category: props.filters.category || '',
    sort: props.filters.sort || 'newest',
    price_min: props.filters.price_min ?? props.priceRange.min ?? '',
    price_max: props.filters.price_max ?? props.priceRange.max ?? '',
});

const productCountText = computed(() => {
    const total = props.products?.total ?? props.products?.data?.length ?? 0;
    return total > 0 ? `${total} produk ditemukan` : 'Belum ada produk tersedia';
});

const applyFilters = () => {
    router.get(route('petshop.index'), {
        search: state.search || undefined,
        category: state.category || undefined,
        sort: state.sort || undefined,
        price_min: state.price_min || undefined,
        price_max: state.price_max || undefined,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const resetFilters = () => {
    state.search = '';
    state.category = '';
    state.sort = 'newest';
    state.price_min = props.priceRange.min ?? '';
    state.price_max = props.priceRange.max ?? '';
    applyFilters();
};

const handleAddToCart = (product) => {
    router.post(route('petshop.cart.items.store'), {
        product_id: product.id,
        quantity: 1,
    }, {
        preserveScroll: true,
    });
};

const formatCurrency = (value) => {
    if (value === null || value === undefined) {
        return 'Rp0';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Petshop" />

    <PublicLayout>
        <section class="bg-gray-50 py-12 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-300">
                            Petshop Klinik Hewan
                        </p>
                        <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                            Produk &amp; Perlengkapan Hewan Kesayangan
                        </h1>
                        <p class="mt-3 max-w-2xl text-base text-gray-600 dark:text-gray-300">
                            Temukan makanan, vitamin, dan perlengkapan terbaik untuk hewan kesayangan Anda. Semua produk dipilih oleh dokter hewan kami.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-medium text-amber-700 shadow-sm dark:border-amber-500/40 dark:bg-amber-900/40 dark:text-amber-100">
                        {{ productCountText }}
                    </div>
                </div>

                <div class="mt-10 grid gap-10 lg:grid-cols-[280px,1fr]">
                    <!-- Filters -->
                    <aside class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <form @submit.prevent="applyFilters" class="space-y-6">
                            <div>
                                <label for="search" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Pencarian</label>
                                <input
                                    id="search"
                                    v-model="state.search"
                                    type="search"
                                    placeholder="Cari nama produk..."
                                    class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                >
                            </div>

                            <div>
                                <label for="category" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Kategori</label>
                                <select
                                    id="category"
                                    v-model="state.category"
                                    @change="applyFilters"
                                    class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option value="">Semua Kategori</option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.slug"
                                    >
                                        {{ category.name }} ({{ category.product_count }})
                                    </option>
                                </select>
                            </div>

                            <div class="grid gap-4">
                                <div>
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Harga Minimum</label>
                                    <input
                                        v-model.number="state.price_min"
                                        type="number"
                                        min="0"
                                        class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                    >
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Harga Maksimum</label>
                                    <input
                                        v-model.number="state.price_max"
                                        type="number"
                                        min="0"
                                        class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                    >
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Rentang harga tersedia: {{ formatCurrency(priceRange.min) }} - {{ formatCurrency(priceRange.max) }}
                                </p>
                            </div>

                            <div>
                                <label for="sort" class="text-sm font-semibold text-gray-700 dark:text-gray-200">Urutkan</label>
                                <select
                                    id="sort"
                                    v-model="state.sort"
                                    @change="applyFilters"
                                    class="mt-2 w-full rounded-lg border border-gray-200 px-4 py-2 text-sm text-gray-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option
                                        v-for="option in sortOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                >
                                    Terapkan Filter
                                </button>
                                <SecondaryButton type="button" class="w-full justify-center" @click="resetFilters">
                                    Reset
                                </SecondaryButton>
                            </div>
                        </form>

                        <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-300">
                            Tips: Gunakan filter harga dan kategori untuk menemukan produk yang paling sesuai dengan kebutuhan hewan kesayangan Anda.
                        </div>
                    </aside>

                    <!-- Product list -->
                    <div>
                        <div v-if="flash.success" class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 shadow-sm dark:border-green-700/40 dark:bg-green-900/40 dark:text-green-200">
                            {{ flash.success }}
                        </div>
                        <div v-if="flash.error" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm dark:border-red-700/40 dark:bg-red-900/40 dark:text-red-200">
                            {{ flash.error }}
                        </div>

                        <div v-if="products.data && products.data.length > 0" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                            <ProductCard
                                v-for="product in products.data"
                                :key="product.id"
                                :product="product"
                                :show-add-button="!product.has_variants"
                                @add-to-cart="handleAddToCart"
                            />
                        </div>

                        <div v-else class="rounded-2xl border border-dashed border-gray-200 bg-white p-10 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-gray-100 text-amber-500 dark:bg-gray-700">
                                <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2.25l1.5 9a2.25 2.25 0 002.25 1.951h7.5A2.25 2.25 0 0018.75 12V10.5m0 0l1.116-6.136A1.125 1.125 0 0018.75 3H6M9 21h.008v.008H9V21zm6 0h.008v.008H15V21z" />
                                </svg>
                            </div>
                            <h2 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                                Produk belum tersedia
                            </h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Silakan cek kembali beberapa saat lagi. Kami terus menambahkan produk baru untuk kebutuhan hewan kesayangan Anda.
                            </p>
                        </div>

                        <Pagination v-if="products.links" :links="products.links" />
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
