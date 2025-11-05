<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    relatedPosts: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const currentUrl = computed(() => {
    if (typeof window !== 'undefined') {
        return window.location.href;
    }
    return page.url;
});
</script>

<template>
    <Head :title="post.title" />

    <PublicLayout>
        <!-- Article Header -->
        <article class="bg-white dark:bg-gray-900">
            <!-- Featured Image -->
            <div v-if="post.featured_image" class="relative h-96 overflow-hidden bg-gray-900">
                <img
                    :src="post.featured_image"
                    :alt="post.title"
                    class="h-full w-full object-cover opacity-80"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
            </div>

            <!-- Article Content -->
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Title and Meta -->
                <div class="-mt-32 relative z-10" :class="!post.featured_image ? 'mt-16' : ''">
                    <div class="rounded-lg bg-white p-8 shadow-xl dark:bg-gray-800">
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white sm:text-5xl" data-aos="fade-up">
                            {{ post.title }}
                        </h1>

                        <!-- Meta Information -->
                        <div class="mt-6 flex flex-wrap items-center gap-6 text-sm text-gray-600 dark:text-gray-400" data-aos="fade-up" data-aos-delay="100">
                            <div class="flex items-center">
                                <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-medium">{{ post.author }}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ post.published_at }}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ post.read_time }}</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>{{ post.views }} views</span>
                            </div>
                        </div>

                        <!-- Excerpt -->
                        <p v-if="post.excerpt" class="mt-6 text-lg text-gray-600 dark:text-gray-300 border-l-4 border-amber-500 pl-4 italic" data-aos="fade-up" data-aos-delay="200">
                            {{ post.excerpt }}
                        </p>
                    </div>
                </div>

                <!-- Article Body -->
                <div class="prose prose-lg prose-amber max-w-none py-12 dark:prose-invert" data-aos="fade-up" data-aos-delay="300">
                    <div v-html="post.content"></div>
                </div>

                <!-- Share & Back Button -->
                <div class="border-t border-gray-200 py-8 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <Link
                            :href="route('blog')"
                            class="inline-flex items-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali ke Blog
                        </Link>

                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Bagikan:</span>
                            <a
                                :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`"
                                target="_blank"
                                class="rounded-full bg-blue-600 p-2 text-white transition-transform hover:scale-110"
                                aria-label="Share on Facebook"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            <a
                                :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(post.title)}`"
                                target="_blank"
                                class="rounded-full bg-sky-500 p-2 text-white transition-transform hover:scale-110"
                                aria-label="Share on Twitter"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                            <a
                                :href="`https://wa.me/?text=${encodeURIComponent(post.title + ' - ' + currentUrl)}`"
                                target="_blank"
                                class="rounded-full bg-green-500 p-2 text-white transition-transform hover:scale-110"
                                aria-label="Share on WhatsApp"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <!-- Related Posts -->
        <div v-if="relatedPosts.length > 0" class="bg-gray-50 py-16 dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white" data-aos="fade-up">
                    Artikel Terkait
                </h2>
                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    <Link
                        v-for="(relatedPost, index) in relatedPosts"
                        :key="relatedPost.id"
                        :href="route('blog.show', relatedPost.slug)"
                        class="group overflow-hidden rounded-lg bg-white shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-2 dark:bg-gray-900"
                        data-aos="fade-up"
                        :data-aos-delay="(index + 1) * 100"
                    >
                        <div class="aspect-[16/9] overflow-hidden bg-gray-200 dark:bg-gray-700">
                            <img
                                v-if="relatedPost.featured_image"
                                :src="relatedPost.featured_image"
                                :alt="relatedPost.title"
                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-amber-500 to-amber-600"
                            >
                                <svg class="h-16 w-16 text-white opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 transition-colors group-hover:text-amber-600 dark:text-white dark:group-hover:text-amber-400">
                                {{ relatedPost.title }}
                            </h3>
                            <p v-if="relatedPost.excerpt" class="mt-2 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                {{ relatedPost.excerpt }}
                            </p>
                            <div class="mt-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ relatedPost.published_at }}</span>
                                <span>{{ relatedPost.read_time }}</span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
/* Custom prose styling for dark mode */
:deep(.prose) {
    @apply text-gray-900 dark:text-gray-100;
}

:deep(.prose h2),
:deep(.prose h3),
:deep(.prose h4) {
    @apply text-gray-900 dark:text-white;
}

:deep(.prose a) {
    @apply text-amber-600 dark:text-amber-400 no-underline hover:underline;
}

:deep(.prose strong) {
    @apply text-gray-900 dark:text-white;
}

:deep(.prose code) {
    @apply bg-gray-100 dark:bg-gray-800 text-amber-600 dark:text-amber-400 rounded px-1;
}

:deep(.prose pre) {
    @apply bg-gray-900 dark:bg-gray-950;
}

:deep(.prose blockquote) {
    @apply border-amber-500 text-gray-700 dark:text-gray-300;
}

:deep(.prose img) {
    @apply rounded-lg shadow-lg;
}
</style>
