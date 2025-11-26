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

// Modal state
const showModal = ref(false);
const selectedImage = ref(null);

const openModal = (image) => {
    selectedImage.value = image;
    showModal.value = true;
    // Prevent body scroll when modal is open
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    showModal.value = false;
    selectedImage.value = null;
    // Restore body scroll
    document.body.style.overflow = '';
};

// Close modal on ESC key
const handleKeydown = (e) => {
    if (e.key === 'Escape' && showModal.value) {
        closeModal();
    }
};

// Debug - lihat apakah parallaxBackground ada
console.log('Parallax Background URL:', props.parallaxBackground);

// Parallax effect
const parallaxOffset = ref(0);

const handleScroll = () => {
    parallaxOffset.value = window.pageYOffset * 0.5; // Parallax speed (0.5 = half speed)
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('keydown', handleKeydown);
    // Cleanup - restore body scroll if component unmounted with modal open
    document.body.style.overflow = '';
});
</script>

<template>
    <Head title="Gallery" />

    <PublicLayout>
        <!-- Parallax Header Section -->
        <div class="relative overflow-hidden min-h-[500px]">
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
            <div class="relative py-32 sm:py-40 z-10">
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
                                @click="openModal(image)"
                                class="translate-y-4 transform rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-900 opacity-0 transition-all group-hover:translate-y-0 group-hover:opacity-100 hover:bg-amber-500 hover:text-white"
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

        <!-- Image Detail Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showModal"
                    class="fixed inset-0 z-50 overflow-y-auto"
                    @click="closeModal"
                >
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black/75 backdrop-blur-sm"></div>

                    <!-- Modal Content -->
                    <div class="flex min-h-full items-center justify-center p-4">
                        <Transition
                            enter-active-class="transition ease-out duration-300"
                            enter-from-class="opacity-0 scale-95"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-200"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <div
                                v-if="showModal && selectedImage"
                                @click.stop
                                class="relative w-full max-w-4xl transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all dark:bg-gray-800"
                            >
                                <!-- Close Button -->
                                <button
                                    @click="closeModal"
                                    class="absolute right-4 top-4 z-10 rounded-full bg-black/50 p-2 text-white transition hover:bg-black/70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                                >
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <!-- Image -->
                                <div class="relative aspect-video w-full overflow-hidden bg-gray-100 dark:bg-gray-900">
                                    <img
                                        v-if="selectedImage.image_url"
                                        :src="selectedImage.image_url"
                                        :alt="selectedImage.title"
                                        class="h-full w-full object-contain"
                                    />
                                    <div v-else class="flex h-full items-center justify-center bg-gradient-to-br from-amber-400 to-amber-600">
                                        <svg
                                            class="h-32 w-32 text-white opacity-50"
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
                                </div>

                                <!-- Content -->
                                <div class="p-6 sm:p-8">
                                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">
                                        {{ selectedImage.title }}
                                    </h2>
                                    <p v-if="selectedImage.description" class="mt-4 text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                                        {{ selectedImage.description }}
                                    </p>
                                    <p v-else class="mt-4 text-base italic text-gray-400 dark:text-gray-500">
                                        Tidak ada deskripsi
                                    </p>

                                    <!-- Action Buttons -->
                                    <div class="mt-6 flex gap-3">
                                        <button
                                            @click="closeModal"
                                            class="rounded-lg bg-gray-200 px-6 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                        >
                                            Tutup
                                        </button>
                                        <a
                                            v-if="selectedImage.image_url"
                                            :href="selectedImage.image_url"
                                            target="_blank"
                                            class="rounded-lg bg-amber-500 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                        >
                                            <span class="flex items-center gap-2">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                                                </svg>
                                                Lihat Ukuran Penuh
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </PublicLayout>
</template>
