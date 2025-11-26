<template>
    <AppLayout title="Booking Berhasil">
        <div class="min-h-screen bg-gradient-to-b from-green-50 to-white py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Success Icon -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-4">
                        <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Booking Berhasil!</h1>
                    <p class="text-gray-600">Terima kasih telah membuat janji temu dengan kami</p>
                </div>

                <!-- Booking Details Card -->
                <div class="bg-white rounded-lg shadow-xl overflow-hidden">
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
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500">Tanggal & Waktu</h3>
                                <p class="text-lg font-semibold text-gray-900">{{ appointment.appointment_date }}</p>
                                <p class="text-gray-600">Pukul {{ appointment.appointment_time }} WIB</p>
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
                                <h3 class="text-sm font-medium text-gray-500">Dokter</h3>
                                <p class="text-lg font-semibold text-gray-900">{{ appointment.doctor.name }}</p>
                                <p class="text-gray-600">{{ appointment.doctor.title }}</p>
                            </div>
                        </div>

                        <!-- Pet -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500">Hewan Peliharaan</h3>
                                <p class="text-lg font-semibold text-gray-900">{{ appointment.pet.name }}</p>
                                <p class="text-gray-600">{{ appointment.pet.species_label }}</p>
                            </div>
                        </div>

                        <!-- Services -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Layanan</h3>
                                <div class="space-y-1">
                                    <span 
                                        v-for="(service, index) in appointment.services" 
                                        :key="index"
                                        class="inline-block bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full mr-2 mb-2"
                                    >
                                        {{ service }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Complaint -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500">Keluhan</h3>
                                <p class="text-gray-900 mt-1">{{ appointment.complaint }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border-t border-blue-100 px-6 py-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1 text-sm text-blue-800">
                                <p class="font-medium mb-1">Informasi Penting:</p>
                                <ul class="list-disc list-inside space-y-1 text-blue-700">
                                    <li>Harap datang 15 menit sebelum waktu appointment</li>
                                    <li>Bawa kode booking ini saat check-in</li>
                                    <li>Anda akan menerima konfirmasi melalui email/WhatsApp</li>
                                    <li>Untuk reschedule, silakan hubungi kami maksimal H-2</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        :href="route('booking.detail', appointment.booking_code)"
                        class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Lihat Status Booking
                    </Link>
                    <Link
                        :href="route('home')"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
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
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    appointment: Object,
});

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
        'no_show': 'bg-gray-100 text-gray-800',
    };
    return classes[props.appointment.status] || 'bg-gray-100 text-gray-800';
});
</script>
