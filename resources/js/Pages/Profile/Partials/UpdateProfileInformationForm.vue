<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
});

const emit = defineEmits(['saved']);

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    photo: null,
});

const verificationLinkSent = ref(null);

const updateProfileInformation = () => {
    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            form.reset('photo');
        },
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const setPhoto = (file) => {
    form.photo = file;
};

const resetPhotoField = () => {
    form.photo = null;
};

defineExpose({
    setPhoto,
    resetPhotoField,
});
</script>

<template>
    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
        <form @submit.prevent="updateProfileInformation" class="p-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Profile Information</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Update your account's profile information and email address.
                </p>
            </div>

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autocomplete="name"
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        autocomplete="username"
                    />
                    <InputError :message="form.errors.email" class="mt-2" />

                    <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Your email address is unverified.

                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="rounded-md text-sm text-amber-600 underline hover:text-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:text-amber-400 dark:hover:text-amber-300 dark:focus:ring-offset-gray-800"
                                @click.prevent="sendEmailVerification"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        <div v-show="verificationLinkSent" class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end space-x-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                <ActionMessage :on="form.recentlySuccessful" class="me-3">
                    <span class="text-sm text-green-600 dark:text-green-400">Saved successfully.</span>
                </ActionMessage>

                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Save Changes
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
