<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NavLink from '@/Components/NavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Footer from '@/Components/Footer.vue';
import WhatsAppButton from '@/Components/WhatsAppButton.vue';
import ThemeSwitch from '@/Components/ThemeSwitch.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const footerSettings = computed(() => page.props.footerSettings || {});
const cartSummary = computed(() => page.props.cartSummary || { total_items: 0, subtotal: 0 });
const flash = computed(() => page.props.flash || {});

const logout = () => {
    router.post(route('logout'));
};

// Mobile menu toggle
const showMobileMenu = ref(false);

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value;
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Navigation -->
        <nav class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('home')">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <NavLink :href="route('home')" :active="route().current('home')">
                                Home
                            </NavLink>
                            <NavLink :href="route('gallery')" :active="route().current('gallery')">
                                Gallery
                            </NavLink>
                            <NavLink :href="route('petshop.index')" :active="route().current('petshop.*')">
                                Petshop
                            </NavLink>
                            <NavLink :href="route('blog')" :active="route().current('blog') || route().current('blog.show')">
                                Blog
                            </NavLink>
                        </div>
                    </div>

                    <!-- Auth Links / Profile Dropdown -->
                    <div class="hidden sm:ms-6 sm:flex sm:items-center">
                        <Link
                            :href="route('petshop.cart.show')"
                            class="relative me-4 inline-flex items-center rounded-full bg-amber-500 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        >
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 2.25h1.386c.51 0 .955.343 1.087.835l.383 1.437m0 0 1.35 5.068m1.65 6.187h9.75m-9.75 0a2.25 2.25 0 11-4.5 0m4.5 0a2.25 2.25 0 01-4.5 0m14.25 0a2.25 2.25 0 11-4.5 0m4.5 0a2.25 2.25 0 01-4.5 0m0 0H7.125m12.75-7.875-1.064 4.256a1.125 1.125 0 01-1.09.844H8.978a1.125 1.125 0 01-1.09-.876l-1.148-4.599M7.5 6.75h13.125" />
                            </svg>
                            <span class="ms-2 hidden md:inline">Keranjang</span>
                            <span
                                v-if="cartSummary.total_items"
                                class="absolute -right-2 -top-2 inline-flex size-5 items-center justify-center rounded-full bg-white text-xs font-bold text-amber-600 shadow"
                            >
                                {{ cartSummary.total_items }}
                            </span>
                        </Link>
                        <template v-if="$page.props.auth.user">
                            <!-- Dark Mode Toggle -->
                            <ThemeSwitch />

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200 dark:border-gray-600" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </template>
                        <template v-else>
                            <!-- Dark Mode Toggle (for guests) -->
                            <ThemeSwitch />

                            <Link
                                :href="route('login')"
                                class="rounded-md px-3 py-2 text-gray-700 ring-1 ring-transparent transition hover:text-gray-900 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Log in
                            </Link>
                            <Link
                                :href="route('register')"
                                class="rounded-md px-3 py-2 text-gray-700 ring-1 ring-transparent transition hover:text-gray-900 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Register
                            </Link>
                        </template>
                    </div>

                    <!-- Hamburger Menu Button (Mobile) -->
                    <div class="flex items-center gap-2 sm:hidden">
                        <!-- Dark Mode Toggle (Mobile) -->
                        <ThemeSwitch />

                        <!-- Hamburger Button -->
                        <button
                            @click="toggleMobileMenu"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out"
                        >
                            <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': showMobileMenu, 'inline-flex': !showMobileMenu }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !showMobileMenu, 'inline-flex': showMobileMenu }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div :class="{'block': showMobileMenu, 'hidden': !showMobileMenu}" class="sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <Link
                        :href="route('home')"
                        :class="{'border-amber-400 bg-amber-50 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300': route().current('home'), 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200': !route().current('home')}"
                        class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out"
                    >
                        Home
                    </Link>
                    <Link
                        :href="route('gallery')"
                        :class="{'border-amber-400 bg-amber-50 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300': route().current('gallery'), 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200': !route().current('gallery')}"
                        class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out"
                    >
                        Gallery
                    </Link>
                    <Link
                        :href="route('petshop.index')"
                        :class="{'border-amber-400 bg-amber-50 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300': route().current('petshop.*'), 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200': !route().current('petshop.*')}"
                        class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out"
                    >
                        Petshop
                    </Link>
                    <Link
                        :href="route('blog')"
                        :class="{'border-amber-400 bg-amber-50 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300': route().current('blog') || route().current('blog.show'), 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200': !route().current('blog') && !route().current('blog.show')}"
                        class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out"
                    >
                        Blog
                    </Link>
                    <Link
                        :href="route('petshop.cart.show')"
                        class="flex items-center gap-2 pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition duration-150 ease-in-out"
                    >
                        Keranjang
                        <span
                            v-if="cartSummary.total_items"
                            class="inline-flex items-center justify-center rounded-full bg-amber-500 px-2 py-0.5 text-xs font-semibold text-white"
                        >
                            {{ cartSummary.total_items }}
                        </span>
                    </Link>
                </div>

                <!-- Mobile Auth Section -->
                <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                    <template v-if="$page.props.auth.user">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ $page.props.auth.user.name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <Link
                                :href="route('profile.show')"
                                class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition duration-150 ease-in-out"
                            >
                                Profile
                            </Link>
                            <Link
                                v-if="$page.props.jetstream.hasApiFeatures"
                                :href="route('api-tokens.index')"
                                class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition duration-150 ease-in-out"
                            >
                                API Tokens
                            </Link>
                            <form @submit.prevent="logout">
                                <button
                                    type="submit"
                                    class="block w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition duration-150 ease-in-out"
                                >
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </template>
                    <template v-else>
                        <div class="space-y-1">
                            <Link
                                :href="route('login')"
                                class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition duration-150 ease-in-out"
                            >
                                Log in
                            </Link>
                            <Link
                                :href="route('register')"
                                class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition duration-150 ease-in-out"
                            >
                                Register
                            </Link>
                        </div>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <div v-if="flash.success || flash.error || flash.info" class="bg-transparent">
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    <div v-if="flash.success" class="mb-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 shadow-sm dark:border-green-700/40 dark:bg-green-900/40 dark:text-green-200">
                        {{ flash.success }}
                    </div>
                    <div v-if="flash.error" class="mb-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm dark:border-red-700/40 dark:bg-red-900/40 dark:text-red-200">
                        {{ flash.error }}
                    </div>
                    <div v-if="flash.info" class="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 shadow-sm dark:border-amber-700/40 dark:bg-amber-900/40 dark:text-amber-200">
                        {{ flash.info }}
                    </div>
                </div>
            </div>
            <slot />
        </main>

        <!-- Footer Component -->
        <Footer :footer-settings="footerSettings" />

        <!-- WhatsApp Floating Button -->
        <WhatsAppButton :phone-number="footerSettings.whatsapp_number" />
    </div>
</template>
