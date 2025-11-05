<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    sliders: {
        type: Array,
        default: () => [],
    },
    doctors: {
        type: Array,
        default: () => [],
    },
    services: {
        type: Array,
        default: () => [],
    },
});

// Fallback placeholder gradients jika tidak ada gambar dari database
const placeholderGradients = [
    'from-blue-500 to-purple-600',
    'from-green-500 to-teal-600',
    'from-orange-500 to-red-600',
    'from-pink-500 to-rose-600',
];

// Gunakan slider dari database atau fallback ke placeholder
const sliderImages = computed(() => {
    if (props.sliders && props.sliders.length > 0) {
        return props.sliders;
    }
    // Fallback placeholder
    return placeholderGradients.map((gradient, index) => ({
        id: index + 1,
        title: null,
        image_url: null,
        gradient: gradient,
    }));
});

const currentSlide = ref(0);
let sliderInterval = null;

const nextSlide = () => {
    currentSlide.value = (currentSlide.value + 1) % sliderImages.value.length;
};

const goToSlide = (index) => {
    currentSlide.value = index;
};

// Typing Effect
const typedText = ref('');
const fullText = 'Selamat Datang di ';
const showCursor = ref(true);
let typingInterval = null;
let cursorInterval = null;

const typeWriter = () => {
    let i = 0;
    typingInterval = setInterval(() => {
        if (i < fullText.length) {
            typedText.value += fullText.charAt(i);
            i++;
        } else {
            clearInterval(typingInterval);
        }
    }, 100); // Kecepatan typing 100ms per karakter
};

onMounted(() => {
    // Auto-slide setiap 5 detik
    if (sliderImages.value.length > 1) {
        sliderInterval = setInterval(nextSlide, 5000);
    }

    // Start typing effect setelah delay singkat
    setTimeout(() => {
        typeWriter();
    }, 500);

    // Blinking cursor effect
    cursorInterval = setInterval(() => {
        showCursor.value = !showCursor.value;
    }, 530);
});

onUnmounted(() => {
    if (sliderInterval) {
        clearInterval(sliderInterval);
    }
    if (typingInterval) {
        clearInterval(typingInterval);
    }
    if (cursorInterval) {
        clearInterval(cursorInterval);
    }
});
</script>

<template>
    <Head title="Home" />

    <PublicLayout>
        <!-- Hero Section with Image Slider -->
        <div class="relative overflow-hidden bg-gray-900">
            <!-- Slider Background -->
            <div class="absolute inset-0">
                <div
                    v-for="(image, index) in sliderImages"
                    :key="image.id"
                    class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                    :class="currentSlide === index ? 'opacity-100' : 'opacity-0'"
                >
                    <!-- Tampilkan gambar asli jika ada, atau gradient placeholder -->
                    <div
                        v-if="image.image_url"
                        class="h-full w-full bg-cover bg-center"
                        :style="{ backgroundImage: `url(${image.image_url})` }"
                    ></div>
                    <div
                        v-else
                        class="h-full w-full bg-gradient-to-br"
                        :class="image.gradient"
                    ></div>
                </div>
                <!-- Overlay untuk readability -->
                <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            </div>

            <!-- Content -->
            <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl md:text-6xl min-h-[4rem]">
                        <span>{{ typedText }}</span>
                        <span v-if="typedText.length < fullText.length" class="animate-pulse">|</span>
                        <span class="text-amber-400">Klinik Hewan</span>
                        <span v-if="typedText.length === fullText.length && showCursor" class="text-amber-400">|</span>
                    </h1>
                    <p
                        class="mx-auto mt-3 max-w-md text-base text-gray-200 sm:text-lg md:mt-5 md:max-w-3xl md:text-xl"
                        data-aos="zoom-in-up"
                        data-aos-delay="100"
                    >
                        Memberikan perawatan terbaik untuk sahabat berbulu Anda dengan layanan medis profesional dan penuh kasih sayang.
                    </p>
                </div>

                <!-- Slider Indicators -->
                <div class="mt-8 flex justify-center space-x-3">
                    <button
                        v-for="(image, index) in sliderImages"
                        :key="'indicator-' + image.id"
                        @click="goToSlide(index)"
                        class="h-3 w-3 rounded-full transition-all duration-300"
                        :class="currentSlide === index ? 'bg-amber-400 w-8' : 'bg-white bg-opacity-50 hover:bg-opacity-75'"
                        :aria-label="'Go to slide ' + (index + 1)"
                    ></button>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="bg-gray-50 py-16 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Layanan Kami
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                        Berbagai layanan kesehatan hewan yang komprehensif
                    </p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <!-- Loop services dari database -->
                    <div
                        v-for="(service, index) in services"
                        :key="service.id"
                        class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-md transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 dark:bg-gray-800"
                        data-aos="fade-up"
                        :data-aos-delay="(index + 1) * 100"
                    >
                        <!-- Decorative background effect on hover -->
                        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>

                        <!-- Content wrapper -->
                        <div class="relative z-10">
                            <!-- Icon with animated background -->
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-md text-white transition-all duration-300 group-hover:scale-110 group-hover:rotate-6"
                                :class="service.icon_color"
                            >
                                <!-- Tampilkan icon custom jika ada -->
                                <img
                                    v-if="service.icon_url"
                                    :src="service.icon_url"
                                    :alt="service.title"
                                    class="h-8 w-8 object-contain transition-transform duration-300 group-hover:scale-110"
                                />
                                <!-- Default SVG icon jika tidak ada -->
                                <svg
                                    v-else
                                    class="h-6 w-6 transition-transform duration-300 group-hover:rotate-12"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>

                            <!-- Title with hover effect -->
                            <h3 class="mt-4 text-xl font-semibold text-gray-900 transition-colors duration-300 group-hover:text-amber-600 dark:text-white dark:group-hover:text-amber-400">
                                {{ service.title }}
                            </h3>

                            <!-- Description -->
                            <p class="mt-2 text-gray-600 dark:text-gray-300">
                                {{ service.description }}
                            </p>

                            <!-- Animated bottom border on hover -->
                            <div class="mt-4 h-1 w-0 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 transition-all duration-300 group-hover:w-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dokter Spesialis Section -->
        <div class="bg-white py-16 dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white" data-aos="fade-up">
                        Dokter Spesialis Kami
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-300" data-aos="fade-up" data-aos-delay="100">
                        Tim dokter hewan profesional dan berpengalaman
                    </p>
                </div>

                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Loop dokter dari database -->
                    <div
                        v-for="(doctor, index) in doctors"
                        :key="doctor.id"
                        class="group relative overflow-hidden rounded-xl bg-gray-50 shadow-lg transition-all duration-300 hover:shadow-2xl dark:bg-gray-700"
                        data-aos="zoom-in"
                        :data-aos-delay="(index + 1) * 100"
                    >
                        <!-- Foto atau Gradient Background -->
                        <div class="aspect-[3/4] overflow-hidden">
                            <!-- Tampilkan foto jika ada -->
                            <div
                                v-if="doctor.photo_url"
                                class="h-full w-full bg-cover bg-center"
                                :style="{ backgroundImage: `url(${doctor.photo_url})` }"
                            ></div>
                            <!-- Tampilkan gradient dengan icon jika tidak ada foto -->
                            <div
                                v-else
                                class="bg-gradient-to-br h-full w-full"
                                :class="doctor.gradient_color"
                            >
                                <div class="flex h-full items-center justify-center">
                                    <svg class="h-32 w-32 text-white opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Info Dokter -->
                        <div class="p-6 text-center">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ doctor.title }} {{ doctor.name }}
                            </h3>
                            <p class="mt-2 text-sm font-medium text-amber-600 dark:text-amber-400">
                                {{ doctor.specialization }}
                            </p>
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ doctor.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-amber-600">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        Siap Merawat Hewan Kesayangan Anda?
                    </h2>
                    <p class="mt-4 text-lg text-amber-100">
                        Hubungi kami untuk membuat janji temu atau konsultasi
                    </p>
                    <div class="mt-8">
                        <a
                            href="#"
                            class="inline-flex items-center rounded-md bg-white px-6 py-3 text-base font-medium text-amber-600 shadow-sm hover:bg-amber-50"
                        >
                            Buat Janji Temu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
