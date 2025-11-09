<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    galleryImages: {
        type: Array,
        default: () => [],
    },
    parallaxBackground: {
        type: String,
        default: null,
    },
});

// Fallback placeholder images
const placeholderImages = [
    { id: 1, title: 'Pemeriksaan Rutin', description: 'Dokter sedang memeriksa kucing', image_url: null },
    { id: 2, title: 'Vaksinasi Anjing', description: 'Proses vaksinasi anjing peliharaan', image_url: null },
    { id: 3, title: 'Grooming Service', description: 'Layanan grooming profesional', image_url: null },
    { id: 4, title: 'Ruang Tunggu', description: 'Ruang tunggu yang nyaman untuk pasien', image_url: null },
    { id: 5, title: 'Perawatan Gigi', description: 'Layanan perawatan gigi hewan', image_url: null },
    { id: 6, title: 'Operasi Minor', description: 'Fasilitas operasi yang modern', image_url: null },
];

const images = props.galleryImages.length > 0 ? props.galleryImages : placeholderImages;

// Debug - lihat apakah parallaxBackground ada
console.log('Parallax Background URL:', props.parallaxBackground);

// Parallax effect
const parallaxOffset = ref(0);

const handleScroll = () => {
    parallaxOffset.value = window.pageYOffset * 0.5; // Parallax speed (0.5 = half speed)
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head title="Gallery" />

    <PublicLayout>
        <!-- Parallax Header Section -->
        <div class="relative overflow-hidden min-h-[400px]">
            <!-- Parallax Background -->
            <div
                v-if="parallaxBackground"
                class="absolute inset-0"
                :style="{ transform: `translateY(${parallaxOffset}px)` }"
            >
                <div
                    class="absolute inset-0 h-[120%] w-full bg-cover bg-center bg-no-repeat bg-fixed"
                    :style="{ backgroundImage: `url(${parallaxBackground})` }"
                ></div>
                <!-- Overlay - sangat transparan -->
                <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-black/20"></div>
            </div>

            <!-- Fallback gradient background if no parallax image -->
            <div
                v-else
                class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600"
            >
                <div class="absolute inset-0 bg-black/20"></div>
            </div>

            <!-- Header Content with text shadow for readability -->
            <div class="relative py-24 sm:py-32 z-10">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h1
                            class="text-4xl font-bold tracking-tight text-white drop-shadow-lg sm:text-5xl md:text-6xl"
                            data-aos="zoom-in-up"
                        >
                            Galeri Klinik Hewan
                        </h1>
                        <p
                            class="mx-auto mt-4 max-w-2xl text-lg text-white drop-shadow-md"
                            data-aos="zoom-in-up"
                            data-aos-delay="100"
                        >
                            Lihat fasilitas dan layanan kami dalam gambar
                        </p>
                    </div>
                </div>
            </div>

            <!-- Curved Bottom Border -->
            <div class="absolute bottom-0 left-0 right-0 z-20">
                <svg viewBox="0 0 1440 80" class="w-full" preserveAspectRatio="none" style="height: 60px;">
                    <path d="M0,0 C480,80 960,80 1440,0 L1440,80 L0,80 Z" fill="rgb(249, 250, 251)" class="dark:fill-gray-900"></path>
                </svg>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="bg-gray-50 py-12 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="image in images"
                        :key="image.id"
                        class="group relative overflow-hidden rounded-lg bg-white shadow-md transition-all hover:shadow-xl dark:bg-gray-800"
                    >
                        <!-- Real Image or Placeholder -->
                        <div v-if="image.image_url" class="aspect-video overflow-hidden">
                            <img
                                :src="image.image_url"
                                :alt="image.title"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                            />
                        </div>
                        <div v-else class="aspect-video bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center">
                            <svg
                                class="h-20 w-20 text-white opacity-50"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>

                        <!-- Image Info -->
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ image.title }}
                            </h3>
                            <p v-if="image.description" class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                {{ image.description }}
                            </p>
                        </div>

                        <!-- Hover Overlay -->
                        <div
                            class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 transition-all group-hover:bg-opacity-30"
                        >
                            <button
                                class="translate-y-4 transform rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-900 opacity-0 transition-all group-hover:translate-y-0 group-hover:opacity-100"
                            >
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="images.length === 0" class="mt-12 text-center">
                    <p class="text-lg text-gray-500 dark:text-gray-400">
                        Belum ada gambar di galeri
                    </p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
