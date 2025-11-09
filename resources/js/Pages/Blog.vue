<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
    headerImage: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <Head title="Blog" />

    <PublicLayout>
        <!-- Header Section with Gradient Overlay -->
        <div class="relative h-96 overflow-hidden bg-gray-800">
            <!-- Background Image (if uploaded) -->
            <img
                v-if="headerImage"
                :src="headerImage"
                alt="Blog Header"
                class="absolute inset-0 h-full w-full object-cover"
            />

            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>

            <!-- Content -->
            <div class="relative z-10 flex h-full items-center">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl md:text-6xl" data-aos="fade-up">
                            Blog Klinik Hewan
                        </h1>
                        <p class="mx-auto mt-3 max-w-md text-base text-white/90 sm:text-lg md:mt-5 md:max-w-3xl md:text-xl" data-aos="fade-up" data-aos-delay="100">
                            Artikel, tips, dan informasi seputar kesehatan dan perawatan hewan kesayangan Anda
                        </p>
                    </div>
                </div>
            </div>

            <!-- Curved Bottom Border -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 80" class="w-full" preserveAspectRatio="none" style="height: 60px;">
                    <path d="M0,0 C480,80 960,80 1440,0 L1440,80 L0,80 Z" fill="rgb(249, 250, 251)" class="dark:fill-gray-900"></path>
                </svg>
            </div>
        </div>

        <!-- Blog Posts Grid -->
        <div class="bg-gray-50 py-16 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Empty State -->
                <div v-if="posts.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Belum Ada Artikel</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Artikel blog akan ditampilkan di sini.</p>
                </div>

                <!-- Posts Grid -->
                <div v-else class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="(post, index) in posts"
                        :key="post.id"
                        :href="route('blog.show', post.slug)"
                        class="group relative overflow-hidden rounded-lg bg-white shadow-md transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 dark:bg-gray-800"
                        data-aos="fade-up"
                        :data-aos-delay="(index + 1) * 100"
                    >
                        <!-- Featured Image -->
                        <div class="aspect-[16/9] overflow-hidden bg-gray-200 dark:bg-gray-700">
                            <img
                                v-if="post.featured_image"
                                :src="post.featured_image"
                                :alt="post.title"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-amber-500 to-amber-600"
                            >
                                <svg class="h-20 w-20 text-white opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <!-- Meta Info -->
                            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center">
                                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ post.published_at }}</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center">
                                        <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ post.read_time }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>{{ post.views }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Title -->
                            <h2 class="mt-4 text-xl font-bold text-gray-900 transition-colors duration-300 group-hover:text-amber-600 dark:text-white dark:group-hover:text-amber-400">
                                {{ post.title }}
                            </h2>

                            <!-- Excerpt -->
                            <p v-if="post.excerpt" class="mt-3 text-gray-600 dark:text-gray-300 line-clamp-3">
                                {{ post.excerpt }}
                            </p>

                            <!-- Author & Read More -->
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>{{ post.author }}</span>
                                </div>
                                <span class="text-sm font-medium text-amber-600 dark:text-amber-400">
                                    Baca Selengkapnya →
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
