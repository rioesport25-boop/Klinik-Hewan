<template>
    <AppLayout title="Riwayat Booking">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Riwayat Booking</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">Lihat semua riwayat janji temu Anda</p>
                </div>

                <!-- Filter Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-8">
                            <button
                                v-for="tab in tabs"
                                :key="tab.value"
                                @click="currentTab = tab.value"
                                :class="[
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                    currentTab === tab.value
                                        ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                        : 'border-transparent text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white hover:border-gray-300 dark:hover:border-gray-600'
                                ]"
                            >
                                {{ tab.label }}
                                <span v-if="getCountByStatus(tab.value) > 0" :class="[
                                    'ml-2 py-0.5 px-2 rounded-full text-xs',
                                    currentTab === tab.value ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'
                                ]">
                                    {{ getCountByStatus(tab.value) }}
                                </span>
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Appointments List -->
                <div v-if="filteredAppointments.length > 0" class="space-y-4">
                    <div
                        v-for="appointment in filteredAppointments"
                        :key="appointment.id"
                        class="bg-white dark:bg-gray-800 dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
                    >
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                <!-- Left Side: Main Info -->
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-3">
                                        <span :class="[
                                            'px-3 py-1 rounded-full text-xs font-semibold',
                                            getStatusBadgeClass(appointment.status)
                                        ]">
                                            {{ getStatusLabel(appointment.status) }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-300">{{ appointment.booking_code }}</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Date & Time -->
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500 dark:text-gray-300">Tanggal & Waktu</p>
                                                <p class="text-gray-900 dark:text-white font-semibold">{{ appointment.appointment_date }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ appointment.appointment_time }} WIB</p>
                                            </div>
                                        </div>

                                        <!-- Doctor -->
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <img 
                                                    :src="appointment.doctor.photo_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(appointment.doctor.name)" 
                                                    :alt="appointment.doctor.name"
                                                    class="w-10 h-10 rounded-full object-cover"
                                                />
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500 dark:text-gray-300">Dokter</p>
                                                <p class="text-gray-900 dark:text-white font-semibold">{{ appointment.doctor.name }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ appointment.doctor.title }}</p>
                                            </div>
                                        </div>

                                        <!-- Pet -->
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center">
                                                    <span class="text-white font-bold text-sm">
                                                        {{ appointment.pet.name.substring(0, 2).toUpperCase() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500 dark:text-gray-300">Hewan Peliharaan</p>
                                                <p class="text-gray-900 dark:text-white font-semibold">{{ appointment.pet.name }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ appointment.pet.species_label }}</p>
                                            </div>
                                        </div>

                                        <!-- Services -->
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-500 dark:text-gray-300 mb-1">Layanan</p>
                                                <div class="flex flex-wrap gap-1">
                                                    <span 
                                                        v-for="(service, index) in appointment.services" 
                                                        :key="index"
                                                        class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs px-2 py-1 rounded-full"
                                                    >
                                                        {{ service }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Side: Actions -->
                                <div class="mt-4 md:mt-0 md:ml-6 flex md:flex-col gap-2">
                                    <Link
                                        :href="route('booking.detail', appointment.booking_code)"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Detail
                                    </Link>
                                    
                                    <button
                                        v-if="appointment.status === 'pending' || appointment.status === 'confirmed'"
                                        @click="cancelAppointment(appointment)"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-red-300 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Batalkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak ada riwayat booking</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">Anda belum memiliki riwayat booking untuk kategori ini</p>
                    <Link
                        :href="route('booking.index')"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat Booking Baru
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { showConfirm, showLoading, closeSwal, showSuccess, showError } from '@/Plugins/sweetalert';

const props = defineProps({
    appointments: Array,
});

const currentTab = ref('all');

const tabs = [
    { label: 'Semua', value: 'all' },
    { label: 'Menunggu', value: 'pending' },
    { label: 'Dikonfirmasi', value: 'confirmed' },
    { label: 'Selesai', value: 'completed' },
    { label: 'Dibatalkan', value: 'cancelled' },
];

const filteredAppointments = computed(() => {
    if (currentTab.value === 'all') {
        return props.appointments;
    }
    return props.appointments.filter(app => app.status === currentTab.value);
});

const getCountByStatus = (status) => {
    if (status === 'all') {
        return props.appointments.length;
    }
    return props.appointments.filter(app => app.status === status).length;
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Menunggu Konfirmasi',
        'confirmed': 'Dikonfirmasi',
        'in_progress': 'Sedang Berlangsung',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan',
        'no_show': 'Tidak Hadir',
    };
    return labels[status] || status;
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-green-100 text-green-800',
        'in_progress': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
        'no_show': 'bg-gray-100 dark:bg-gray-700 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-800';
};

const cancelAppointment = async (appointment) => {
    const result = await showConfirm(
        `Apakah Anda yakin ingin membatalkan booking untuk ${appointment.pet.name} pada ${appointment.appointment_date}?`,
        'Batalkan Booking',
        'Ya, Batalkan',
        'Tidak'
    );

    if (result.isConfirmed) {
        showLoading('Membatalkan booking...');
        
        router.post(route('booking.cancel', appointment.id), {}, {
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
</script>
