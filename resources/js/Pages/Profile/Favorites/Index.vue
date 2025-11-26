<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { showConfirm, showSuccess } from '@/Plugins/sweetalert';

defineProps({
    favorites: {
        type: Array,
        default: () => [],
    },
});

const removeFavorite = (favoriteId) => {
    showConfirm({
        title: 'Hapus dari Favorit?',
        text: 'Apakah Anda yakin ingin menghapus produk ini dari favorit?',
        icon: 'warning',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('profile.favorites.destroy', favoriteId), {
                preserveScroll: true,
                onSuccess: () => {
                    showSuccess('Produk berhasil dihapus dari favorit');
                }
            });
        }
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <AppLayout title="Produk Favorit">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Produk Favorit
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="mb-6">
                            <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                                Produk Favorit Saya
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Daftar produk yang Anda sukai
                            </p>
                        </div>

                        <div v-if="favorites.length === 0" class="text-center py-12">
                            <svg class="mx-auto size-24 text-gray-400 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                                Belum ada produk favorit
                            </h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Mulai tambahkan produk ke favorit untuk melihatnya di sini
                            </p>
                            <div class="mt-6">
                                <Link
                                    :href="route('petshop.index')"
                                    class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                >
                                    <svg class="-ml-0.5 mr-1.5 size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                                    </svg>
                                    Jelajahi Produk
                                </Link>
                            </div>
                        </div>

                        <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <div
                                v-for="favorite in favorites"
                                :key="favorite.id"
                                class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-lg dark:border-gray-700 dark:bg-gray-800"
                            >
                                <!-- Product Image -->
                                <Link
                                    :href="route('petshop.product.show', favorite.product.slug)"
                                    class="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-700"
                                >
                                    <img
                                        v-if="favorite.product.image"
                                        :src="favorite.product.image"
                                        :alt="favorite.product.name"
                                        class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    />
                                    <div v-else class="flex size-full items-center justify-center">
                                        <svg class="size-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                    </div>

                                    <!-- Stock Badge -->
                                    <div v-if="favorite.product.stock <= 0" class="absolute inset-0 flex items-center justify-center bg-black/50">
                                        <span class="rounded-full bg-red-600 px-4 py-1.5 text-sm font-semibold text-white">
                                            Stok Habis
                                        </span>
                                    </div>
                                </Link>

                                <!-- Remove Button -->
                                <button
                                    type="button"
                                    @click="removeFavorite(favorite.id)"
                                    class="absolute right-2 top-2 z-10 rounded-full bg-white/90 p-2 text-red-500 shadow-md transition hover:bg-red-50 dark:bg-gray-800/90 dark:hover:bg-red-900/30"
                                >
                                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                    </svg>
                                </button>

                                <!-- Product Info -->
                                <div class="flex flex-1 flex-col p-4">
                                    <div class="mb-2">
                                        <span v-if="favorite.product.category" class="text-xs font-medium text-amber-600 dark:text-amber-400">
                                            {{ favorite.product.category.name }}
                                        </span>
                                    </div>

                                    <Link
                                        :href="route('petshop.product.show', favorite.product.slug)"
                                        class="mb-2 line-clamp-2 text-sm font-semibold text-gray-900 hover:text-amber-600 dark:text-white dark:hover:text-amber-400"
                                    >
                                        {{ favorite.product.name }}
                                    </Link>

                                    <div class="mt-auto">
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ formatCurrency(favorite.product.price) }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Ditambahkan {{ favorite.created_at }}
                                        </p>
                                    </div>

                                    <!-- View Product Button -->
                                    <Link
                                        :href="route('petshop.product.show', favorite.product.slug)"
                                        class="mt-3 rounded-lg bg-amber-600 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                    >
                                        Lihat Produk
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
