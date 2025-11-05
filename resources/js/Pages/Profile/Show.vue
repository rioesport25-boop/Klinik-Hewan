<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

const page = usePage();
const activeTab = ref('profile');
const photoInput = ref(null);
const photoPreview = ref(null);
const profileForm = ref(null);

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
    profileForm.value?.setPhoto(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            profileForm.value?.resetPhotoField();
            if (photoInput.value) {
                photoInput.value.value = '';
            }
        },
    });
};

const handleProfileSaved = () => {
    photoPreview.value = null;
    profileForm.value?.resetPhotoField();
    if (photoInput.value) {
        photoInput.value.value = '';
    }
};

const tabs = computed(() => {
    const baseTabs = [
        { id: 'profile', name: 'Profile Information', icon: 'user' },
        { id: 'security', name: 'Security', icon: 'shield' },
        { id: 'sessions', name: 'Browser Sessions', icon: 'computer' },
    ];

    if (page.props.jetstream?.hasAccountDeletionFeatures) {
        baseTabs.push({ id: 'danger', name: 'Delete Account', icon: 'trash' });
    }

    return baseTabs;
});
</script>

<template>
    <AppLayout title="Profile">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Profile Header -->
                <div class="mb-8 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="relative h-32 bg-gradient-to-r from-amber-500 to-amber-600">
                        <!-- Profile Photo -->
                        <div class="absolute -bottom-12 left-8">
                            <div class="relative cursor-pointer" @click="$page.props.jetstream.managesProfilePhotos ? selectNewPhoto() : null">
                                <img
                                    v-show="!photoPreview"
                                    :src="$page.props.auth.user.profile_photo_url"
                                    :alt="$page.props.auth.user.name"
                                    class="size-24 rounded-full border-4 border-white object-cover shadow-lg transition-opacity hover:opacity-75 dark:border-gray-800"
                                />
                                <div v-show="photoPreview" class="size-24 overflow-hidden rounded-full border-4 border-white shadow-lg dark:border-gray-800">
                                    <img
                                        :src="photoPreview"
                                        :alt="$page.props.auth.user.name"
                                        class="h-full w-full object-cover"
                                    />
                                </div>

                                <!-- Camera Icon Badge -->
                                <button
                                    v-if="$page.props.jetstream.managesProfilePhotos"
                                    @click.stop="selectNewPhoto"
                                    class="absolute bottom-0 right-0 flex size-8 items-center justify-center rounded-full bg-amber-500 text-white shadow-lg transition-transform hover:scale-110 hover:bg-amber-600"
                                    title="Change photo"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>

                                <!-- Hidden file input -->
                                <input
                                    ref="photoInput"
                                    type="file"
                                    class="hidden"
                                    accept="image/*"
                                    @change="updatePhotoPreview"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start justify-between px-8 pb-6 pt-16">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $page.props.auth.user.name }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ $page.props.auth.user.email }}
                            </p>
                        </div>

                        <!-- Delete Photo Button -->
                        <button
                            v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth.user.profile_photo_path"
                            @click="deletePhoto"
                            class="mt-2 rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
                        >
                            Remove Photo
                        </button>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="mb-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="[
                                    activeTab === tab.id
                                        ? 'border-amber-500 text-amber-600 dark:border-amber-400 dark:text-amber-400'
                                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300',
                                    'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium transition-colors duration-200'
                                ]"
                            >
                                <!-- Icon placeholders - you can add actual icons here -->
                                <svg
                                    v-if="tab.icon === 'user'"
                                    :class="[
                                        activeTab === tab.id ? 'text-amber-500 dark:text-amber-400' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                                        '-ml-0.5 mr-2 size-5'
                                    ]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <svg
                                    v-else-if="tab.icon === 'shield'"
                                    :class="[
                                        activeTab === tab.id ? 'text-amber-500 dark:text-amber-400' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                                        '-ml-0.5 mr-2 size-5'
                                    ]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <svg
                                    v-else-if="tab.icon === 'computer'"
                                    :class="[
                                        activeTab === tab.id ? 'text-amber-500 dark:text-amber-400' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                                        '-ml-0.5 mr-2 size-5'
                                    ]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <svg
                                    v-else-if="tab.icon === 'trash'"
                                    :class="[
                                        activeTab === tab.id ? 'text-amber-500 dark:text-amber-400' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                                        '-ml-0.5 mr-2 size-5'
                                    ]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span>{{ tab.name }}</span>
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Tab Content -->
                <div>
                    <!-- Profile Information Tab -->
                    <div v-show="activeTab === 'profile'">
                        <UpdateProfileInformationForm
                            v-if="$page.props.jetstream.canUpdateProfileInformation"
                            ref="profileForm"
                            :user="$page.props.auth.user"
                            @saved="handleProfileSaved"
                        />
                    </div>

                    <!-- Security Tab -->
                    <div v-show="activeTab === 'security'" class="space-y-6">
                        <UpdatePasswordForm v-if="$page.props.jetstream.canUpdatePassword" />
                        <TwoFactorAuthenticationForm
                            v-if="$page.props.jetstream.canManageTwoFactorAuthentication"
                            :requires-confirmation="confirmsTwoFactorAuthentication"
                        />
                    </div>

                    <!-- Browser Sessions Tab -->
                    <div v-show="activeTab === 'sessions'">
                        <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                    </div>

                    <!-- Delete Account Tab -->
                    <div v-show="activeTab === 'danger'" v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
