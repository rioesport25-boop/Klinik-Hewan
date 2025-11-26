<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const showDropdown = ref(false);
const notifications = ref([]);
const loading = ref(false);

const unreadCount = computed(() => page.props.unreadNotificationsCount || 0);

const fetchNotifications = async () => {
    if (loading.value) return;
    
    loading.value = true;
    try {
        const response = await axios.get(route('notifications.index'));
        notifications.value = response.data.notifications;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    } finally {
        loading.value = false;
    }
};

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
    
    if (showDropdown.value && notifications.value.length === 0) {
        fetchNotifications();
    }
};

const markAsRead = async (notification) => {
    if (notification.is_read) return;
    
    try {
        await axios.post(route('notifications.mark-as-read', notification.id));
        notification.is_read = true;
        router.reload({ only: ['unreadNotificationsCount'] });
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post(route('notifications.mark-all-as-read'));
        notifications.value.forEach(n => n.is_read = true);
        router.reload({ only: ['unreadNotificationsCount'] });
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

const closeDropdown = () => {
    showDropdown.value = false;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    const dropdown = document.getElementById('notification-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div id="notification-dropdown" class="relative">
        <!-- Notification Bell Button -->
        <button
            type="button"
            @click="toggleDropdown"
            class="relative rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300 dark:focus:ring-offset-gray-800"
        >
            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>

            <!-- Unread Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -right-1 -top-1 flex size-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown Panel -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showDropdown"
                class="absolute right-0 z-50 mt-2 w-96 origin-top-right rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Notifikasi
                    </h3>
                    <button
                        v-if="unreadCount > 0"
                        type="button"
                        @click="markAllAsRead"
                        class="text-xs font-medium text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300"
                    >
                        Tandai semua dibaca
                    </button>
                </div>

                <!-- Content -->
                <div class="max-h-96 overflow-y-auto">
                    <!-- Loading State -->
                    <div v-if="loading" class="flex items-center justify-center py-12">
                        <div class="size-8 animate-spin rounded-full border-4 border-amber-500 border-t-transparent"></div>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="notifications.length === 0" class="py-12 text-center">
                        <svg class="mx-auto size-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                            Belum ada notifikasi
                        </p>
                    </div>

                    <!-- Notification List -->
                    <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                        <button
                            v-for="notification in notifications"
                            :key="notification.id"
                            type="button"
                            @click="markAsRead(notification)"
                            class="flex w-full gap-3 p-4 text-left transition hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            :class="!notification.is_read ? 'bg-amber-50/50 dark:bg-amber-900/10' : ''"
                        >
                            <!-- Product Image -->
                            <div class="flex-shrink-0">
                                <img
                                    v-if="notification.data?.product_image"
                                    :src="notification.data.product_image"
                                    :alt="notification.data.product_name"
                                    class="size-12 rounded-lg object-cover"
                                />
                                <div v-else class="flex size-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                    <svg class="size-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Notification Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ notification.title }}
                                    </p>
                                    <span
                                        v-if="!notification.is_read"
                                        class="size-2 flex-shrink-0 rounded-full bg-amber-500"
                                    ></span>
                                </div>
                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    {{ notification.message }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                    {{ notification.created_at }}
                                </p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
