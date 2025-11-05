<script setup>
import ProductCard from '@/Components/Petshop/ProductCard.vue';
import QuantitySelector from '@/Components/Petshop/QuantitySelector.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    relatedProducts: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const placeholderImage = 'https://ui-avatars.com/api/?name=Petshop&background=EBF4FF&color=7F9CF5';
const selectedImage = ref(props.product.images?.[0]?.url || placeholderImage);
const quantity = ref(1);
const selectedVariantId = ref(
    props.product.variants && props.product.variants.length === 1
        ? props.product.variants[0].id
        : null,
);
const variantError = ref('');

const productHasVariants = computed(() => props.product.variants && props.product.variants.length > 0);

const selectedVariant = computed(() =>
    props.product.variants?.find((variant) => variant.id === selectedVariantId.value) ?? null,
);

const maxQuantity = computed(() => {
    const stock = selectedVariant.value ? selectedVariant.value.stock : props.product.stock;
    return Math.max(stock || 0, 0);
});

const inStock = computed(() => maxQuantity.value > 0);

const displayPrice = computed(() => {
    if (selectedVariant.value) {
        return selectedVariant.value.final_price || props.product.price;
    }
    return props.product.price;
});

const displayComparePrice = computed(() => {
    if (selectedVariant.value && selectedVariant.value.final_price && props.product.compare_price) {
        return props.product.compare_price;
    }
    return props.product.compare_price;
});

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

const selectVariant = (variant) => {
    if (variant.stock === 0) {
        return;
    }
    selectedVariantId.value = variant.id;
};

const setActiveImage = (url) => {
    selectedImage.value = url || placeholderImage;
};

const addToCart = () => {
    variantError.value = '';

    if (productHasVariants.value && !selectedVariantId.value) {
        variantError.value = 'Silakan pilih varian terlebih dahulu.';
        return;
    }

    if (!inStock.value) {
        variantError.value = 'Stok produk sedang tidak tersedia.';
        return;
    }

    router.post(route('petshop.cart.items.store'), {
        product_id: props.product.id,
        variant_id: selectedVariantId.value,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
    });
};

watch(selectedVariantId, () => {
    variantError.value = '';
    if (selectedVariant.value && selectedVariant.value.stock > 0 && quantity.value > selectedVariant.value.stock) {
        quantity.value = selectedVariant.value.stock;
    }
});

watch(maxQuantity, (value) => {
    if (value > 0 && quantity.value > value) {
        quantity.value = value;
    }
});

watch(quantity, (value) => {
    if (value < 1) {
        quantity.value = 1;
    }
    if (maxQuantity.value > 0 && value > maxQuantity.value) {
        quantity.value = maxQuantity.value;
    }
});
</script>

<template>
    <Head :title="product.name" />

    <PublicLayout>
        <section class="bg-white py-12 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2">
                    <!-- Gallery -->
                    <div>
                        <div class="overflow-hidden rounded-3xl border border-gray-100 bg-gray-100 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <img
                                :src="selectedImage"
                                :alt="product.name"
                                class="h-full w-full object-cover"
                            >
                        </div>
                        <div v-if="product.images && product.images.length > 1" class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-5">
                            <button
                                v-for="image in product.images"
                                :key="image.id"
                                type="button"
                                @click="setActiveImage(image.url)"
                                :class="[
                                    'overflow-hidden rounded-xl border transition focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900',
                                    selectedImage === image.url ? 'border-amber-500 ring-2 ring-amber-200 dark:ring-amber-500/40' : 'border-transparent',
                                ]"
                            >
                                <img :src="image.url" :alt="product.name" class="h-20 w-full object-cover">
                            </button>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="space-y-6">
                        <div>
                            <div class="flex items-center gap-3">
                                <span v-if="product.category?.name" class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-600 dark:bg-amber-900/40 dark:text-amber-200">
                                    {{ product.category.name }}
                                </span>
                                <span v-if="product.is_featured" class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white shadow">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111 5.518.403a.562.562 0 01.318.986l-4.204 3.602 1.285 5.385a.562.562 0 01-.84.61L12 16.902l-4.722 2.694a.562.562 0 01-.84-.61l1.285-5.386-4.204-3.6a.562.562 0 01.318-.986l5.518-.403 2.125-5.112z" />
                                    </svg>
                                    Unggulan
                                </span>
                            </div>
                            <h1 class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ product.name }}
                            </h1>
                            <p v-if="product.description" class="mt-4 text-gray-600 dark:text-gray-300">
                                {{ product.description }}
                            </p>
                        </div>

                        <div class="rounded-3xl border border-gray-100 bg-gray-50 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-300">
                                        Harga
                                    </p>
                                    <div class="mt-2 flex items-baseline gap-3">
                                        <span class="text-3xl font-bold text-amber-600 dark:text-amber-300">
                                            {{ formatCurrency(displayPrice) }}
                                        </span>
                                        <span
                                            v-if="displayComparePrice && displayComparePrice > displayPrice"
                                            class="text-base text-gray-400 line-through dark:text-gray-500"
                                        >
                                            {{ formatCurrency(displayComparePrice) }}
                                        </span>
                                        <span
                                            v-if="product.discount_percentage"
                                            class="inline-flex rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-600 dark:text-amber-300"
                                        >
                                            Hemat {{ product.discount_percentage }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Stok
                                    </p>
                                    <p
                                        :class="[
                                            'mt-2 text-sm font-semibold',
                                            inStock ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-400',
                                        ]"
                                    >
                                        {{ inStock ? `Tersedia (${maxQuantity} item)` : 'Stok Habis' }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="productHasVariants" class="mt-6">
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                    Pilih Varian
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button
                                        v-for="variant in product.variants"
                                        :key="variant.id"
                                        type="button"
                                        @click="selectVariant(variant)"
                                        :disabled="variant.stock === 0"
                                        :class="[
                                            'rounded-xl border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900',
                                            selectedVariantId === variant.id
                                                ? 'border-amber-500 bg-amber-50 text-amber-600 dark:border-amber-400 dark:bg-amber-900/40 dark:text-amber-200'
                                                : 'border-gray-200 text-gray-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-600 dark:border-gray-700 dark:text-gray-200 dark:hover:border-amber-500 dark:hover:bg-amber-900/20',
                                            variant.stock === 0 ? 'cursor-not-allowed opacity-50 dark:opacity-40' : '',
                                        ]"
                                    >
                                        <span class="block text-left">
                                            {{ variant.name || variant.size || variant.color || 'Varian' }}
                                        </span>
                                        <span class="block text-xs text-gray-400 dark:text-gray-300">
                                            {{ formatCurrency(variant.final_price) }}
                                            · Stok {{ variant.stock }}
                                        </span>
                                    </button>
                                </div>
                                <p v-if="variantError" class="mt-2 text-xs font-medium text-red-500">
                                    {{ variantError }}
                                </p>
                            </div>

                            <div class="mt-6 flex flex-wrap items-center gap-4">
                                <QuantitySelector
                                    v-model="quantity"
                                    :max="Math.max(maxQuantity, 1)"
                                    size="lg"
                                />
                                <button
                                    type="button"
                                    :disabled="!inStock"
                                    @click="addToCart"
                                    class="inline-flex flex-1 items-center justify-center rounded-2xl bg-amber-500 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-amber-500/30 transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-gray-400 disabled:shadow-none dark:focus:ring-offset-gray-900"
                                >
                                    <svg class="me-2 size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437m0 0 1.35 5.068m1.65 6.187h9.75m-9.75 0a2.25 2.25 0 11-4.5 0m4.5 0a2.25 2.25 0 01-4.5 0m14.25 0a2.25 2.25 0 11-4.5 0m4.5 0a2.25 2.25 0 01-4.5 0m0 0H7.125m12.75-7.875-1.064 4.256a1.125 1.125 0 01-1.09.844H8.978a1.125 1.125 0 01-1.09-.876l-1.148-4.599M7.5 6.75h13.125" />
                                    </svg>
                                    Tambah ke Keranjang
                                </button>
                            </div>

                            <div v-if="!inStock" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400">
                                Produk sedang tidak tersedia. Silakan hubungi admin untuk informasi restock.
                            </div>
                        </div>

                        <div v-if="product.specifications" class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Spesifikasi Produk
                            </h2>
                            <div class="prose prose-sm mt-3 max-w-none text-gray-600 dark:prose-invert dark:text-gray-300" v-html="product.specifications" />
                        </div>

                        <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Informasi Tambahan
                            </h2>
                            <dl class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="font-medium text-gray-700 dark:text-gray-200">Berat</dt>
                                    <dd>{{ product.weight ? `${product.weight} gram` : '—' }}</dd>
                                </div>
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="font-medium text-gray-700 dark:text-gray-200">Total Terjual</dt>
                                    <dd>{{ product.order_count }} produk</dd>
                                </div>
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="font-medium text-gray-700 dark:text-gray-200">Ulasan</dt>
                                    <dd>{{ product.review_count }} ulasan · Rating {{ Number(product.rating_average || 0).toFixed(1) }}/5</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div v-if="product.reviews && product.reviews.length > 0" class="mt-16">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Ulasan Pelanggan
                        </h2>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ product.review_count }} ulasan · Rating rata-rata {{ Number(product.rating_average || 0).toFixed(1) }}/5
                        </span>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <article
                            v-for="review in product.reviews"
                            :key="review.id"
                            class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ review.user?.name || 'Pelanggan' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ review.created_at }}</p>
                                </div>
                                <div class="flex items-center gap-1 text-amber-500">
                                    <svg
                                        v-for="index in review.rating"
                                        :key="index"
                                        class="size-3.5"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111 5.518.403a.562.562 0 01.318.986l-4.204 3.602 1.285 5.385a.562.562 0 01-.84.61L12 16.902l-4.722 2.694a.562.562 0 01-.84-.61l1.285-5.386-4.204-3.6a.562.562 0 01.318-.986l5.518-.403 2.125-5.112z" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ review.review }}
                            </p>
                        </article>
                    </div>
                </div>

                <div v-if="relatedProducts && relatedProducts.length > 0" class="mt-16">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Produk Terkait
                        </h2>
                        <SecondaryButton @click="router.visit(route('petshop.index'))">
                            Lihat Semua Produk
                        </SecondaryButton>
                    </div>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <ProductCard
                            v-for="related in relatedProducts"
                            :key="related.id"
                            :product="related"
                        />
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
