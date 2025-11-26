<template>
    <AppLayout title="Detail Booking">
        <div class="min-h-screen bg-gradient-to-b from-blue-50 dark:from-gray-900 to-white dark:to-gray-900 py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <Link
                    :href="route('booking.history')"
                    class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white mb-6 transition-colors"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Riwayat
                </Link>

                <!-- Status Icon -->
                <div class="text-center mb-8">
                    <div :class="[
                        'inline-flex items-center justify-center w-24 h-24 rounded-full mb-4',
                        statusIconBgClass
                    ]">
                        <svg class="w-12 h-12" :class="statusIconColorClass" fill="currentColor" viewBox="0 0 20 20">
                            <path v-if="appointment.status === 'completed'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            <path v-else-if="appointment.status === 'cancelled'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Detail Booking</h1>
                    <p class="text-gray-600 dark:text-gray-300">{{ statusMessage }}</p>
                </div>

                <!-- Booking Details Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <div class="flex items-center justify-between text-white">
                            <div>
                                <p class="text-sm opacity-90">Kode Booking</p>
                                <p class="text-2xl font-bold">{{ appointment.booking_code }}</p>
                            </div>
                            <div :class="[
                                'px-4 py-2 rounded-full text-sm font-semibold',
                                statusBadgeClass
                            ]">
                                {{ statusLabel }}
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-6 space-y-6">
                        <!-- Date & Time -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal & Waktu</h3>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ appointment.appointment_date }}</p>
                                <p class="text-gray-600 dark:text-gray-300">Pukul {{ appointment.appointment_time }} WIB</p>
                            </div>
                        </div>

                        <!-- Doctor -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <img 
                                    :src="appointment.doctor.photo_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(appointment.doctor.name)" 
                                    :alt="appointment.doctor.name"
                                    class="w-12 h-12 rounded-full object-cover"
                                />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Dokter</h3>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ appointment.doctor.name }}</p>
                                <p class="text-gray-600 dark:text-gray-300">{{ appointment.doctor.title }}</p>
                            </div>
                        </div>

                        <!-- Pet -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-lg">
                                        {{ appointment.pet.name.substring(0, 2).toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Hewan Peliharaan</h3>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ appointment.pet.name }}</p>
                                <p class="text-gray-600 dark:text-gray-300">{{ appointment.pet.species_label }}</p>
                            </div>
                        </div>

                        <!-- Services -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Layanan</h3>
                                <div class="space-y-1">
                                    <span 
                                        v-for="(service, index) in appointment.services" 
                                        :key="index"
                                        class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm px-3 py-1 rounded-full mr-2 mb-2"
                                    >
                                        {{ service }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Complaint -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Keluhan</h3>
                                <p class="text-gray-900 dark:text-white mt-1">{{ appointment.complaint }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div v-if="appointment.status === 'pending' || appointment.status === 'confirmed'" class="bg-blue-50 dark:bg-blue-900/30 border-t border-blue-100 dark:border-blue-800 px-6 py-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1 text-sm text-blue-800 dark:text-blue-200">
                                <p class="font-medium mb-1">Informasi Penting:</p>
                                <ul class="list-disc list-inside space-y-1 text-blue-700 dark:text-blue-300">
                                    <li>Harap datang 15 menit sebelum waktu appointment</li>
                                    <li>Bawa kode booking ini saat check-in</li>
                                    <li>Anda akan menerima konfirmasi melalui email/WhatsApp</li>
                                    <li>Untuk reschedule, silakan hubungi kami maksimal H-2</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review Section for Completed Appointments -->
                <div v-if="appointment.status === 'completed'" class="mt-8">
                    <!-- Existing Review -->
                    <div v-if="hasReview" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Ulasan Anda</h3>
                        <div class="flex items-center mb-3">
                            <div class="flex items-center">
                                <svg 
                                    v-for="star in 5" 
                                    :key="star"
                                    class="w-6 h-6"
                                    :class="star <= appointment.review.rating ? 'text-yellow-400' : 'text-gray-300'"
                                    fill="currentColor" 
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <span class="ml-2 text-sm text-gray-600">{{ appointment.review.rating }} dari 5</span>
                        </div>
                        <p class="text-gray-700">{{ appointment.review.comment }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Diulas pada {{ appointment.review.created_at }}</p>
                    </div>

                    <!-- Review Form -->
                    <div v-else>
                        <div v-if="!showReviewForm" class="text-center">
                            <button
                                @click="showReviewForm = true"
                                class="inline-flex items-center justify-center px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors font-medium shadow-lg"
                            >
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Beri Rating & Ulasan
                            </button>
                        </div>

                        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold text-gray-900">Beri Rating & Ulasan</h3>
                                <button
                                    @click="showReviewForm = false"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Star Rating -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Rating</label>
                                <div class="flex items-center space-x-2">
                                    <button
                                        v-for="star in 5"
                                        :key="star"
                                        @click="setRating(star)"
                                        @mouseenter="hoverRating = star"
                                        @mouseleave="hoverRating = 0"
                                        type="button"
                                        class="transition-transform hover:scale-110 focus:outline-none"
                                    >
                                        <svg 
                                            class="w-10 h-10"
                                            :class="star <= (hoverRating || rating) ? 'text-yellow-400' : 'text-gray-300'"
                                            fill="currentColor" 
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                </div>
                                <p v-if="rating > 0" class="mt-2 text-sm text-gray-600">
                                    {{ rating === 1 ? 'Sangat Buruk' : rating === 2 ? 'Buruk' : rating === 3 ? 'Cukup' : rating === 4 ? 'Baik' : 'Sangat Baik' }}
                                </p>
                            </div>

                            <!-- Comment -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ulasan</label>
                                <textarea
                                    v-model="comment"
                                    rows="4"
                                    placeholder="Tulis ulasan Anda tentang pelayanan kami..."
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent resize-none"
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500">Minimal 10 karakter</p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex gap-3">
                                <button
                                    @click="submitReview"
                                    :disabled="submitting"
                                    class="flex-1 bg-yellow-500 text-white py-3 rounded-lg hover:bg-yellow-600 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ submitting ? 'Mengirim...' : 'Kirim Ulasan' }}
                                </button>
                                <button
                                    @click="showReviewForm = false"
                                    type="button"
                                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                                >
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                    <button
                        v-if="appointment.status === 'pending' || appointment.status === 'confirmed'"
                        @click="cancelAppointment"
                        class="inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batalkan Booking
                    </button>
                    
                    <Link
                        :href="route('home')"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Kembali ke Home
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { showConfirm, showLoading, closeSwal, showSuccess, showError } from '@/Plugins/sweetalert';

const props = defineProps({
    appointment: Object,
});

const showReviewForm = ref(false);
const rating = ref(0);
const hoverRating = ref(0);
const comment = ref('');
const submitting = ref(false);

const hasReview = computed(() => props.appointment.review !== null);

const statusLabel = computed(() => {
    const labels = {
        'pending': 'Menunggu Konfirmasi',
        'confirmed': 'Dikonfirmasi',
        'in_progress': 'Sedang Berlangsung',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan',
        'no_show': 'Tidak Hadir',
    };
    return labels[props.appointment.status] || props.appointment.status;
});

const statusBadgeClass = computed(() => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-green-100 text-green-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
        'no_show': 'bg-gray-100 dark:bg-gray-700 text-gray-800',
    };
    return classes[props.appointment.status] || 'bg-gray-100 dark:bg-gray-700 text-gray-800';
});

const statusIconBgClass = computed(() => {
    const classes = {
        'completed': 'bg-green-100',
        'cancelled': 'bg-red-100',
        'default': 'bg-blue-100',
    };
    return classes[props.appointment.status] || classes.default;
});

const statusIconColorClass = computed(() => {
    const classes = {
        'completed': 'text-green-600',
        'cancelled': 'text-red-600',
        'default': 'text-blue-600',
    };
    return classes[props.appointment.status] || classes.default;
});

const statusMessage = computed(() => {
    const messages = {
        'pending': 'Booking Anda sedang menunggu konfirmasi',
        'confirmed': 'Booking Anda telah dikonfirmasi',
        'in_progress': 'Appointment sedang berlangsung',
        'completed': 'Appointment telah selesai',
        'cancelled': 'Booking telah dibatalkan',
        'no_show': 'Tidak hadir pada waktu yang ditentukan',
    };
    return messages[props.appointment.status] || 'Status booking Anda';
});

const cancelAppointment = async () => {
    const result = await showConfirm(
        `Apakah Anda yakin ingin membatalkan booking untuk ${props.appointment.pet.name} pada ${props.appointment.appointment_date}?`,
        'Batalkan Booking',
        'Ya, Batalkan',
        'Tidak'
    );

    if (result.isConfirmed) {
        showLoading('Membatalkan booking...');
        
        router.post(route('booking.cancel', props.appointment.id), {}, {
            onSuccess: () => {
                closeSwal();
                showSuccess('Booking berhasil dibatalkan', 'Berhasil');
            },
            onError: (errors) => {
                closeSwal();
                const errorMessage = errors.message || Object.values(errors)[0] || 'Gagal membatalkan booking';
                showError(errorMessage, 'Error');
            }
        });
    }
};

const setRating = (value) => {
    rating.value = value;
};

const submitReview = async () => {
    if (rating.value === 0) {
        showError('Silakan pilih rating bintang terlebih dahulu', 'Rating Diperlukan');
        return;
    }

    if (!comment.value.trim()) {
        showError('Silakan tulis ulasan Anda', 'Ulasan Diperlukan');
        return;
    }

    submitting.value = true;
    showLoading('Mengirim ulasan...');

    router.post(route('booking.review', props.appointment.id), {
        rating: rating.value,
        comment: comment.value,
    }, {
        onSuccess: () => {
            closeSwal();
            showSuccess('Terima kasih atas ulasan Anda!', 'Berhasil');
            showReviewForm.value = false;
            rating.value = 0;
            comment.value = '';
        },
        onError: (errors) => {
            closeSwal();
            const errorMessage = errors.message || Object.values(errors)[0] || 'Gagal mengirim ulasan';
            showError(errorMessage, 'Error');
        },
        onFinish: () => {
            submitting.value = false;
        }
    });
};
</script>
