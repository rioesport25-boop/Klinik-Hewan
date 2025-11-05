<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});

const decodeLabel = (label) => {
    return label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&amp;/g, '&')
        .replace(/&nbsp;/g, ' ')
        .replace(/<[^>]*>?/gm, '')
        .trim();
};

const processedLinks = computed(() => props.links.map((link) => ({
    ...link,
    label: decodeLabel(link.label),
})));
</script>

<template>
    <nav v-if="processedLinks.length > 1" class="mt-8 flex justify-center" role="navigation" aria-label="Pagination">
        <ul class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <li v-for="(link, index) in processedLinks" :key="index">
                <span
                    v-if="!link.url"
                    :class="[
                        'inline-flex min-w-9 items-center justify-center rounded-full px-3 py-2 text-xs font-semibold transition',
                        link.active
                            ? 'bg-amber-500 text-white'
                            : 'text-gray-400 dark:text-gray-500',
                    ]"
                >
                    {{ link.label }}
                </span>
                <Link
                    v-else
                    :href="link.url"
                    :class="[
                        'inline-flex min-w-9 items-center justify-center rounded-full px-3 py-2 text-xs font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900',
                        link.active
                            ? 'bg-amber-500 text-white shadow'
                            : 'text-gray-600 hover:bg-amber-50 hover:text-amber-600 dark:text-gray-300 dark:hover:bg-amber-900/30 dark:hover:text-amber-300',
                    ]"
                    preserve-scroll
                >
                    {{ link.label }}
                </Link>
            </li>
        </ul>
    </nav>
</template>
