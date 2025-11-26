<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    doctors: {
        type: Array,
        default: () => [],
    },
    currentWeek: {
        type: Array,
        default: () => [],
    },
    weekRange: {
        type: String,
        default: '',
    },
    today: {
        type: String,
        default: '',
    },
});

// Selected day state
const selectedDay = ref(null);

// Get schedules for selected day
const schedulesForDay = computed(() => {
    if (!selectedDay.value) return [];
    
    return props.doctors.map(doctor => {
        const daySchedules = doctor.schedules.filter(s => s.day_of_week === selectedDay.value.dayKey);
        return {
            ...doctor,
            daySchedules,
        };
    }).filter(d => d.daySchedules.length > 0);
});

// Select a day
const selectDay = (day) => {
    selectedDay.value = day;
};

// Auto-select today or first day with schedules
const autoSelectDay = () => {
    const today = props.currentWeek.find(d => d.isToday);
    if (today) {
        // Check if today has any schedules
        const todayHasSchedules = props.doctors.some(doctor => 
            doctor.schedules.some(s => s.day_of_week === today.dayKey)
        );
        if (todayHasSchedules) {
            selectedDay.value = today;
            return;
        }
    }
    
    // Find first day with schedules
    for (const day of props.currentWeek) {
        const hasSchedules = props.doctors.some(doctor => 
            doctor.schedules.some(s => s.day_of_week === day.dayKey)
        );
        if (hasSchedules) {
            selectedDay.value = day;
            return;
        }
    }
};

// Auto-select on mount
autoSelectDay();

// Check if day has any schedules (excluding holidays)
const dayHasSchedules = (dayKey) => {
    const day = props.currentWeek.find(d => d.dayKey === dayKey);
    
    // If it's a holiday, return false (clinic closed)
    if (day?.isHoliday) {
        return false;
    }
    
    return props.doctors.some(doctor => 
        doctor.schedules.some(s => s.day_of_week === dayKey)
    );
};

// Check if we should show "Tutup" status
const shouldShowClosedStatus = (day) => {
    // Only show "Tutup" for past days or today
    // Don't show for future days unless it's manually set as holiday
    const dayDate = new Date(day.fullDate);
    const todayDate = new Date(props.today);
    dayDate.setHours(0, 0, 0, 0);
    todayDate.setHours(0, 0, 0, 0);
    
    return dayDate <= todayDate;
};

// Helper for getting doctor schedules grouped by day (not used in calendar view)
const getDoctorSchedulesByDay = (doctor) => {
    const dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    const groupedSchedules = {};
    
    doctor.schedules.forEach(schedule => {
        if (!groupedSchedules[schedule.day_of_week]) {
            groupedSchedules[schedule.day_of_week] = [];
        }
        groupedSchedules[schedule.day_of_week].push(schedule);
    });
    
    return dayOrder
        .filter(day => groupedSchedules[day])
        .map(day => ({
            day: day,
            dayName: groupedSchedules[day][0].day_name,
            schedules: groupedSchedules[day],
        }));
};
</script>

<template>
    <Head title="Jadwal Dokter" />

    <PublicLayout>
        <!-- Header Section -->
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl md:text-6xl" data-aos="fade-up">
                        Jadwal Dokter
                    </h1>
                    <p class="mx-auto mt-3 max-w-md text-base text-white/90 sm:text-lg md:mt-5 md:max-w-3xl md:text-xl" data-aos="fade-up" data-aos-delay="100">
                        Jadwal praktik dokter hewan kami untuk melayani hewan kesayangan Anda
                    </p>
                    <p class="mt-4 text-white/90 font-semibold">
                        Minggu: {{ weekRange }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Calendar Week View -->
        <div class="bg-gray-50 py-8 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden" data-aos="fade-up">
                    <div class="grid grid-cols-7 gap-0">
                        <div 
                            v-for="day in currentWeek" 
                            :key="day.dayKey"
                            @click="dayHasSchedules(day.dayKey) ? selectDay(day) : null"
                            :class="[
                                'p-4 text-center transition-all duration-200 border-r border-b border-gray-200 dark:border-gray-700 last:border-r-0',
                                selectedDay?.dayKey === day.dayKey 
                                    ? 'bg-amber-500 text-white shadow-lg' 
                                    : dayHasSchedules(day.dayKey)
                                        ? 'cursor-pointer hover:bg-amber-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-800'
                                        : 'bg-gray-100 dark:bg-gray-900 opacity-50 cursor-not-allowed',
                                day.isToday && selectedDay?.dayKey !== day.dayKey
                                    ? 'ring-2 ring-amber-400 dark:ring-amber-500'
                                    : ''
                            ]"
                        >
                            <div class="text-xs font-semibold uppercase mb-1"
                                 :class="selectedDay?.dayKey === day.dayKey ? 'text-white' : 'text-gray-500 dark:text-gray-400'">
                                {{ day.dayName }}
                            </div>
                            <div class="text-2xl font-bold"
                                 :class="selectedDay?.dayKey === day.dayKey ? 'text-white' : 'text-gray-900 dark:text-white'">
                                {{ day.dayNumber }}
                            </div>
                            <div class="text-xs"
                                 :class="selectedDay?.dayKey === day.dayKey ? 'text-amber-100' : 'text-gray-500 dark:text-gray-400'">
                                {{ day.monthName }}
                            </div>
                            <div v-if="day.isToday" class="mt-1">
                                <span :class="[
                                    'text-xs px-2 py-0.5 rounded-full',
                                    selectedDay?.dayKey === day.dayKey 
                                        ? 'bg-white text-amber-600' 
                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200'
                                ]">
                                    Hari ini
                                </span>
                            </div>
                            <div v-if="day.isHoliday" class="mt-1">
                                <span :class="[
                                    'text-xs px-2 py-0.5 rounded-full font-semibold',
                                    selectedDay?.dayKey === day.dayKey 
                                        ? 'bg-white text-red-600' 
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                ]">
                                    Libur
                                </span>
                            </div>
                            <div v-if="!dayHasSchedules(day.dayKey) && !day.isHoliday && shouldShowClosedStatus(day)" class="mt-1">
                                <span class="text-xs text-gray-400 dark:text-gray-600">Tutup</span>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-4 text-center">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Klik pada tanggal untuk melihat jadwal dokter di hari tersebut
                </p>
            </div>
        </div>

        <!-- Selected Day Schedules -->
        <div class="bg-gray-50 pb-16 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="selectedDay">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center" data-aos="fade-up">
                        Jadwal {{ selectedDay.isToday ? 'Hari Ini ' : '' }}{{ selectedDay.dayName }}, {{ selectedDay.dayNumber }} {{ selectedDay.monthName }} {{ selectedDay.fullDate ? new Date(selectedDay.fullDate).getFullYear() : '' }}
                    </h2>

                    <!-- Holiday Notice -->
                    <div v-if="selectedDay.isHoliday" class="mb-8" data-aos="fade-up">
                        <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-xl p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-12 h-12 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-red-900 dark:text-red-100 mb-2">
                                        {{ selectedDay.holiday.name }}
                                    </h3>
                                    <p class="text-red-800 dark:text-red-200 text-lg mb-2">
                                        Klinik Tutup - Hari Libur
                                    </p>
                                    <p v-if="selectedDay.holiday.description" class="text-red-700 dark:text-red-300 text-sm">
                                        {{ selectedDay.holiday.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="schedulesForDay.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="(doctor, index) in schedulesForDay" 
                            :key="doctor.id"
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300"
                            data-aos="fade-up"
                            :data-aos-delay="(index + 1) * 100"
                        >
                            <!-- Doctor Header -->
                            <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-6 text-white">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="h-16 w-16 overflow-hidden rounded-full border-4 border-white shadow-lg">
                                            <img 
                                                v-if="doctor.photo_url"
                                                :src="doctor.photo_url" 
                                                :alt="doctor.name"
                                                class="h-full w-full object-cover"
                                            >
                                            <div 
                                                v-else
                                                :class="`flex h-full w-full items-center justify-center text-2xl font-bold ${doctor.gradient_color || 'bg-gradient-to-br from-blue-400 to-blue-600'}`"
                                            >
                                                {{ doctor.name.charAt(0) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-xl font-bold truncate">{{ doctor.name }}</h3>
                                        <p class="text-white/90 text-sm">{{ doctor.title }}</p>
                                        <p class="text-white/80 text-xs">{{ doctor.specialization }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Schedule Times -->
                            <div class="p-6">
                                <div class="space-y-3">
                                    <div 
                                        v-for="schedule in doctor.daySchedules" 
                                        :key="schedule.id"
                                        class="flex items-center p-3 bg-amber-50 dark:bg-gray-700 rounded-lg hover:bg-amber-100 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <span class="text-gray-900 dark:text-white font-medium block">
                                                {{ schedule.start_time }} - {{ schedule.end_time }}
                                            </span>
                                            <span v-if="schedule.notes" class="text-xs text-gray-600 dark:text-gray-400">
                                                {{ schedule.notes }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12" data-aos="fade-up">
                        <svg class="w-20 h-20 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">
                            {{ selectedDay.isHoliday ? 'Klinik tutup pada hari libur' : 'Tidak ada jadwal dokter untuk hari ini' }}
                        </p>
                        <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">
                            {{ selectedDay.isHoliday ? 'Silakan kunjungi kami di hari kerja' : 'Silakan pilih hari lain dari kalender di atas' }}
                        </p>
                    </div>
                </div>

                <!-- Info Footer -->
                <div class="mt-12 rounded-xl border-2 border-dashed border-amber-300 bg-amber-50 p-6 dark:border-amber-700 dark:bg-amber-900/20" data-aos="fade-up">
                    <div class="flex items-start gap-3">
                        <svg class="h-6 w-6 flex-shrink-0 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <div class="flex-1">
                            <h3 class="font-semibold text-amber-900 dark:text-amber-200">Informasi Penting</h3>
                            <ul class="mt-2 text-sm text-amber-800 dark:text-amber-300 space-y-1">
                                <li>• Jadwal dokter diperbarui setiap minggu</li>
                                <li>• Untuk konsultasi, harap membuat janji terlebih dahulu</li>
                                <li>• Dalam keadaan darurat, segera hubungi nomor emergency kami</li>
                                <li>• Jadwal dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
