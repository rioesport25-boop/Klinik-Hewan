<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    showAddButton: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['add-to-cart']);

const placeholderImage = 'https://ui-avatars.com/api/?name=Petshop&background=EBF4FF&color=7F9CF5';

const primaryImage = computed(() => props.product.primary_image_url || placeholderImage);
const canAddToCart = computed(() => props.showAddButton && !props.product.has_variants);
const isLoggedIn = computed(() => !!page.props.auth.user);
const isFavorited = computed(() => {
    const favIds = page.props.favoriteProductIds || [];
    return favIds.includes(props.product.id);
});

const isTogglingFavorite = ref(false);

const toggleFavorite = async () => {
    if (!isLoggedIn.value) {
        router.visit(route('login'));
        return;
    }

    isTogglingFavorite.value = true;

    router.post(
        route('profile.favorites.toggle', props.product.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isTogglingFavorite.value = false;
            },
        }
    );
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
    <div class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-gray-700/60 dark:bg-gray-800">
        <div class="relative aspect-square overflow-hidden">
            <img
                :src="primaryImage"
                :alt="product.name"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            >

            <!-- Favorite Button -->
            <button
                type="button"
                @click="toggleFavorite"
                :disabled="isTogglingFavorite"
                class="absolute left-3 top-3 z-10 rounded-full bg-white/90 p-2 shadow-md transition hover:scale-110 hover:bg-white disabled:opacity-50 dark:bg-gray-800/90 dark:hover:bg-gray-800"
                :class="isFavorited ? 'text-red-500' : 'text-gray-400 hover:text-red-500'"
            >
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" :fill="isFavorited ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </button>

            <div v-if="product.discount_percentage" class="absolute right-3 top-3 rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white shadow">
                -{{ product.discount_percentage }}%
            </div>

            <div v-if="product.is_featured" class="absolute bottom-3 right-3 flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-amber-700 shadow dark:bg-amber-900/50 dark:text-amber-200">
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111 5.518.403a.562.562 0 01.318.986l-4.204 3.602 1.285 5.385a.562.562 0 01-.84.61L12 16.902l-4.722 2.694a.562.562 0 01-.84-.61l1.285-5.386-4.204-3.6a.562.562 0 01.318-.986l5.518-.403 2.125-5.112z" />
                </svg>
                Unggulan
            </div>
        </div>

        <div class="flex flex-1 flex-col p-5">
            <div class="flex items-center justify-between text-xs uppercase tracking-wide text-amber-600 dark:text-amber-300">
                <span v-if="product.category?.name">
                    {{ product.category.name }}
                </span>
                <span v-if="product.rating_average" class="flex items-center gap-1 text-gray-500 dark:text-gray-300">
                    <svg class="size-3.5 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111 5.518.403a.562.562 0 01.318.986l-4.204 3.602 1.285 5.385a.562.562 0 01-.84.61L12 16.902l-4.722 2.694a.562.562 0 01-.84-.61l1.285-5.386-4.204-3.6a.562.562 0 01.318-.986l5.518-.403 2.125-5.112z" />
                    </svg>
                    {{ Number(product.rating_average).toFixed(1) }}
                </span>
            </div>

            <h3 class="mt-2 line-clamp-2 text-lg font-semibold text-gray-900 transition-colors duration-200 group-hover:text-amber-600 dark:text-white dark:group-hover:text-amber-300">
                {{ product.name }}
            </h3>

            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-xl font-bold text-amber-600 dark:text-amber-400">
                    {{ formatCurrency(product.price) }}
                </span>
                <span
                    v-if="product.compare_price && product.compare_price > product.price"
                    class="text-sm text-gray-400 line-through dark:text-gray-500"
                >
                    {{ formatCurrency(product.compare_price) }}
                </span>
            </div>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                Stok: <span class="font-medium text-gray-700 dark:text-gray-200">{{ product.stock }}</span>
            </p>

            <div v-if="product.has_variants" class="mt-1 text-xs font-medium uppercase text-amber-600 dark:text-amber-300">
                Tersedia variasi produk
            </div>

            <div class="mt-auto flex flex-col gap-3 pt-5">
                <Link
                    :href="route('petshop.product.show', product.slug)"
                    class="inline-flex items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:border-amber-700/60 dark:bg-amber-900/30 dark:text-amber-200 dark:hover:border-amber-500 dark:hover:bg-amber-900/50 dark:focus:ring-offset-gray-900"
                >
                    Lihat Detail
                </Link>

                <button
                    v-if="canAddToCart"
                    type="button"
                    @click="emit('add-to-cart', product)"
                    class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                >
                    <svg class="me-2 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437m0 0 1.35 5.068m1.65 6.187h9.75m-9.75 0a2.25 2.25 0 11-4.5 0m4.5 0a2.25 2.25 0 01-4.5 0m14.25 0a2.25 2.25 0 11-4.5 0m4.5 0a2.25 2.25 0 01-4.5 0m0 0H7.125m12.75-7.875-1.064 4.256a1.125 1.125 0 01-1.09.844H8.978a1.125 1.125 0 01-1.09-.876l-1.148-4.599M7.5 6.75h13.125" />
                    </svg>
                    Tambah ke Keranjang
                </button>

                <div v-else-if="showAddButton" class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-2 text-center text-xs font-medium text-gray-500 dark:border-gray-600 dark:bg-gray-800/60 dark:text-gray-300">
                    Pilih varian melalui halaman detail produk
                </div>
            </div>
        </div>
    </div>
</template>
