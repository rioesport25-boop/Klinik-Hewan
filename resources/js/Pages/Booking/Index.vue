<template>
    <AppLayout title="Pendaftaran Online">
        <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Pendaftaran Online</h1>
                    <p class="text-gray-600">Buat janji temu dengan dokter hewan kami dengan mudah</p>
                </div>

                <!-- Progress Steps -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div v-for="(step, index) in steps" :key="index" class="flex-1">
                            <div class="flex items-center">
                                <div class="flex items-center relative">
                                    <div :class="[
                                        'rounded-full transition duration-500 ease-in-out h-12 w-12 flex items-center justify-center border-2',
                                        currentStep > index ? 'bg-green-500 border-green-500' : 
                                        currentStep === index ? 'bg-blue-500 border-blue-500' : 
                                        'bg-white border-gray-300'
                                    ]">
                                        <svg v-if="currentStep > index" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span v-else :class="[
                                            'text-sm font-semibold',
                                            currentStep === index ? 'text-white' : 'text-gray-500'
                                        ]">{{ index + 1 }}</span>
                                    </div>
                                    <div class="text-xs font-medium mt-16 absolute -bottom-8 left-1/2 transform -translate-x-1/2 whitespace-nowrap">
                                        {{ step.title }}
                                    </div>
                                </div>
                                <div v-if="index < steps.length - 1" :class="[
                                    'flex-auto border-t-2 transition duration-500 ease-in-out mx-2',
                                    currentStep > index ? 'border-green-500' : 'border-gray-300'
                                ]"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-lg shadow-xl p-8 mt-16">
                    <form @submit.prevent="handleSubmit">
                        <!-- Step 1: Select Date -->
                        <div v-show="currentStep === 0" class="space-y-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Pilih Tanggal Kunjungan</h2>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                                <input 
                                    type="date" 
                                    v-model="form.appointment_date"
                                    :min="minDate"
                                    :max="maxDate"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    📅 Periode minggu ini: {{ formatDate(minDate) }} - {{ formatDate(maxDate) }}
                                </p>
                                <p class="mt-1 text-xs text-amber-600">
                                    ⚠️ Hanya dapat memilih tanggal pada minggu ini ({{ formatDate(minDate) }} - {{ formatDate(maxDate) }})
                                </p>
                                <p v-if="form.errors.appointment_date" class="mt-1 text-sm text-red-600">{{ form.errors.appointment_date }}</p>
                            </div>
                        </div>

                        <!-- Step 2: Select Time Slot -->
                        <div v-show="currentStep === 1" class="space-y-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Pilih Waktu</h2>
                            
                            <div class="text-center py-8">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-gray-600">Pilih waktu yang tersedia</p>
                                <p class="text-sm text-gray-500 mt-2">Waktu tersedia akan ditampilkan setelah Anda memilih dokter</p>
                            </div>

                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                <button
                                    v-for="slot in availableTimeSlots"
                                    :key="slot.time"
                                    type="button"
                                    @click="form.appointment_time = slot.time"
                                    :class="[
                                        'py-3 px-4 rounded-lg border-2 transition-all font-medium',
                                        form.appointment_time === slot.time 
                                            ? 'border-blue-500 bg-blue-500 text-white' 
                                            : 'border-gray-200 hover:border-blue-300 text-gray-700'
                                    ]"
                                >
                                    {{ slot.formatted }}
                                </button>
                            </div>
                            <p v-if="form.errors.appointment_time" class="mt-1 text-sm text-red-600">{{ form.errors.appointment_time }}</p>
                        </div>

                        <!-- Step 3: Select Doctor -->
                        <div v-show="currentStep === 2" class="space-y-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Pilih Dokter</h2>
                            
                            <div v-if="loadingSlots" class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                <p class="mt-2 text-gray-600">Memuat dokter yang tersedia...</p>
                            </div>

                            <div v-else-if="filteredDoctors.length === 0" class="text-center py-8">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <p class="text-gray-600">Tidak ada dokter yang praktik pada tanggal dan waktu yang dipilih.</p>
                                <p class="text-sm text-gray-500 mt-2">Silakan pilih tanggal atau waktu lain.</p>
                            </div>

                            <div v-else class="grid grid-cols-1 gap-4">
                                <div 
                                    v-for="doctor in filteredDoctors" 
                                    :key="doctor.id"
                                    @click="selectDoctor(doctor)"
                                    :class="[
                                        'p-4 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md',
                                        form.doctor_id === doctor.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                >
                                    <div class="flex items-center space-x-4">
                                        <img 
                                            :src="doctor.photo_url || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doctor.name)" 
                                            :alt="doctor.name"
                                            class="w-16 h-16 rounded-full object-cover"
                                        />
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ doctor.name }}</h3>
                                            <p class="text-sm text-gray-600">{{ doctor.title }}</p>
                                            <p class="text-sm text-blue-600">{{ doctor.specialization }}</p>
                                        </div>
                                        <div v-if="form.doctor_id === doctor.id" class="text-blue-500">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors.doctor_id" class="mt-1 text-sm text-red-600">{{ form.errors.doctor_id }}</p>
                        </div>

                        <!-- Step 4: Select Services -->
                        <div v-show="currentStep === 3" class="space-y-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Pilih Layanan</h2>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div 
                                    v-for="service in services" 
                                    :key="service.id"
                                    @click="toggleService(service.id)"
                                    :class="[
                                        'p-4 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md',
                                        form.service_ids.includes(service.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                >
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div :class="[
                                                'w-10 h-10 rounded-lg flex items-center justify-center',
                                                form.service_ids.includes(service.id) ? 'bg-blue-500' : 'bg-gray-100'
                                            ]">
                                                <svg class="w-6 h-6" :class="form.service_ids.includes(service.id) ? 'text-white' : 'text-gray-600'" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ service.title }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">{{ service.description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors.service_ids" class="mt-1 text-sm text-red-600">{{ form.errors.service_ids }}</p>
                        </div>

                        <!-- Step 5: Pet Information -->
                        <div v-show="currentStep === 4" class="space-y-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Data Hewan Peliharaan</h2>
                            
                            <!-- Select existing pet or add new -->
                            <div v-if="userPets.length > 0" class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Hewan</label>
                                <div class="grid grid-cols-1 gap-3 mb-4">
                                    <div 
                                        v-for="pet in userPets" 
                                        :key="pet.id"
                                        :class="[
                                            'p-3 border-2 rounded-lg transition-all flex items-center space-x-3',
                                            form.pet_id === pet.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                                        ]"
                                    >
                                        <div 
                                            @click="selectPet(pet)"
                                            class="flex items-center space-x-3 flex-1 cursor-pointer"
                                        >
                                            <div class="relative">
                                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-lg shadow-md">
                                                    {{ pet.name.substring(0, 2).toUpperCase() }}
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-gray-900">{{ pet.name }}</h4>
                                                <p class="text-sm text-gray-600">{{ pet.species_label }} • {{ formatPetAge(pet.birth_date) }}</p>
                                            </div>
                                        </div>
                                        
                                        <!-- Action buttons -->
                                        <div class="flex items-center space-x-2">
                                            <button
                                                type="button"
                                                @click.stop="openEditModal(pet)"
                                                class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                                                title="Edit"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                type="button"
                                                @click.stop="deletePet(pet)"
                                                class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                                title="Hapus"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            <div v-if="form.pet_id === pet.id" class="text-blue-500">
                                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button 
                                    type="button"
                                    @click="showNewPetForm = !showNewPetForm; form.pet_id = null;"
                                    class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium text-sm"
                                >
                                    <svg v-if="!showNewPetForm" class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <svg v-else class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    {{ showNewPetForm ? 'Pilih dari daftar' : 'Tambah hewan baru' }}
                                </button>
                            </div>

                            <!-- New Pet Form -->
                            <div v-if="showNewPetForm || userPets.length === 0" class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Hewan *</label>
                                        <input 
                                            type="text" 
                                            v-model="form.pet_name"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Hewan *</label>
                                        <select 
                                            v-model="form.pet_species"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            required
                                        >
                                            <option value="">Pilih jenis</option>
                                            <option value="dog">Anjing</option>
                                            <option value="cat">Kucing</option>
                                            <option value="bird">Burung</option>
                                            <option value="rabbit">Kelinci</option>
                                            <option value="hamster">Hamster</option>
                                            <option value="other">Lainnya</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ras/Breed</label>
                                        <input 
                                            type="text" 
                                            v-model="form.pet_breed"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                                        <select 
                                            v-model="form.pet_gender"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        >
                                            <option value="">Pilih jenis kelamin</option>
                                            <option value="male">Jantan</option>
                                            <option value="female">Betina</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                                        <input 
                                            type="date" 
                                            v-model="form.pet_birth_date"
                                            :max="today"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Berat (kg)</label>
                                        <input 
                                            type="number" 
                                            v-model="form.pet_weight"
                                            step="0.1"
                                            min="0"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Complaint -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keluhan / Tujuan Kunjungan *</label>
                                <textarea 
                                    v-model="form.complaint"
                                    rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Jelaskan keluhan atau tujuan kunjungan Anda"
                                    required
                                ></textarea>
                                <p v-if="form.errors.complaint" class="mt-1 text-sm text-red-600">{{ form.errors.complaint }}</p>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-8 flex justify-between">
                            <button
                                v-if="currentStep > 0"
                                type="button"
                                @click="previousStep"
                                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium"
                            >
                                ← Kembali
                            </button>
                            <div v-else></div>

                            <button
                                v-if="currentStep < steps.length - 1"
                                type="button"
                                @click="nextStep"
                                :disabled="!canProceed"
                                :class="[
                                    'px-6 py-3 rounded-lg transition-colors font-medium',
                                    canProceed 
                                        ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                ]"
                            >
                                Lanjut →
                            </button>

                            <button
                                v-else
                                type="submit"
                                :disabled="form.processing || !canProceed"
                                :class="[
                                    'px-6 py-3 rounded-lg transition-colors font-medium',
                                    form.processing || !canProceed
                                        ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                                        : 'bg-green-600 text-white hover:bg-green-700'
                                ]"
                            >
                                <span v-if="form.processing">Memproses...</span>
                                <span v-else>✓ Konfirmasi Booking</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { showError, showLoading, closeSwal, showConfirm, showSuccess } from '@/Plugins/sweetalert';

const props = defineProps({
    services: Array,
    doctors: Array,
    userPets: Array,
    weekStart: String,
    weekEnd: String,
    today: String,
});

const steps = [
    { title: 'Pilih Tanggal' },
    { title: 'Pilih Waktu' },
    { title: 'Pilih Dokter' },
    { title: 'Pilih Layanan' },
    { title: 'Data Hewan' },
];

const currentStep = ref(0);
const loadingSlots = ref(false);
const availableSlots = ref([]);
const showNewPetForm = ref(false);

const today = props.today;
const minDate = props.today;
const maxDate = props.weekEnd;

// Generate available time slots based on doctors' schedules for selected date
const availableTimeSlots = computed(() => {
    if (!form.appointment_date) return [];
    
    // Get day of week from selected date
    const selectedDate = new Date(form.appointment_date);
    const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    const dayOfWeek = dayNames[selectedDate.getDay()];
    
    // Find all doctors who practice on this day
    const doctorsSchedules = props.doctors
        .map(doctor => {
            const schedule = doctor.schedules.find(s => s.day_of_week === dayOfWeek);
            return schedule ? {
                start_time: schedule.start_time,
                end_time: schedule.end_time
            } : null;
        })
        .filter(s => s !== null);
    
    if (doctorsSchedules.length === 0) return [];
    
    // Get the earliest start time and latest end time from all doctors
    const startTimes = doctorsSchedules.map(s => s.start_time);
    const endTimes = doctorsSchedules.map(s => s.end_time);
    const earliestStart = startTimes.sort()[0];
    const latestEnd = endTimes.sort().reverse()[0];
    
    // Generate slots between earliest start and latest end
    const slots = [];
    const [startHour, startMinute] = earliestStart.split(':').map(Number);
    const [endHour, endMinute] = latestEnd.split(':').map(Number);
    
    let currentHour = startHour;
    let currentMinute = startMinute;
    
    while (currentHour < endHour || (currentHour === endHour && currentMinute < endMinute)) {
        const timeStr = `${currentHour.toString().padStart(2, '0')}:${currentMinute.toString().padStart(2, '0')}`;
        
        // Check if at least one doctor is available at this time
        const isAvailable = doctorsSchedules.some(schedule => {
            return timeStr >= schedule.start_time && timeStr < schedule.end_time;
        });
        
        if (isAvailable) {
            slots.push({
                time: timeStr,
                formatted: timeStr
            });
        }
        
        // Add 30 minutes
        currentMinute += 30;
        if (currentMinute >= 60) {
            currentMinute = 0;
            currentHour++;
        }
    }
    
    return slots;
});

// Filtered doctors based on selected date and time
const filteredDoctors = computed(() => {
    if (!form.appointment_date || !form.appointment_time) {
        return [];
    }
    
    // Get day of week from selected date
    const selectedDate = new Date(form.appointment_date);
    const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    const dayOfWeek = dayNames[selectedDate.getDay()];
    
    // Filter doctors who have schedules on that day and time
    return props.doctors.filter(doctor => {
        return doctor.schedules.some(schedule => {
            if (schedule.day_of_week !== dayOfWeek) return false;
            
            // Check if selected time is within doctor's schedule
            const selectedTime = form.appointment_time;
            return selectedTime >= schedule.start_time && selectedTime < schedule.end_time;
        });
    });
});

const form = useForm({
    appointment_date: '',
    doctor_id: null,
    appointment_time: '',
    service_ids: [],
    pet_id: null,
    pet_name: '',
    pet_species: '',
    pet_breed: '',
    pet_birth_date: '',
    pet_gender: '',
    pet_weight: null,
    complaint: '',
});

const canProceed = computed(() => {
    switch (currentStep.value) {
        case 0: 
            // Validate date is within current week
            if (!form.appointment_date) return false;
            const selectedDate = new Date(form.appointment_date);
            const min = new Date(minDate);
            const max = new Date(maxDate);
            return selectedDate >= min && selectedDate <= max;
        case 1: return !!form.appointment_time;
        case 2: return !!form.doctor_id;
        case 3: return form.service_ids.length > 0;
        case 4: 
            if (form.pet_id) return !!form.complaint;
            return !!form.pet_name && !!form.pet_species && !!form.complaint;
        default: return false;
    }
});

const selectDoctor = (doctor) => {
    form.doctor_id = doctor.id;
};

const toggleService = (serviceId) => {
    const index = form.service_ids.indexOf(serviceId);
    if (index > -1) {
        form.service_ids.splice(index, 1);
    } else {
        form.service_ids.push(serviceId);
    }
};

const selectPet = (pet) => {
    form.pet_id = pet.id;
    showNewPetForm.value = false;
    // Clear new pet form
    form.pet_name = '';
    form.pet_species = '';
    form.pet_breed = '';
    form.pet_birth_date = '';
    form.pet_gender = '';
    form.pet_weight = null;
};

const loadAvailableSlots = async () => {
    if (!form.doctor_id || !form.appointment_date) return;

    loadingSlots.value = true;
    try {
        const response = await axios.get('/booking/slots', {
            params: {
                doctor_id: form.doctor_id,
                date: form.appointment_date,
            }
        });

        availableSlots.value = response.data.slots || [];
        
        if (!response.data.available && response.data.message) {
            showError(response.data.message, 'Tidak Tersedia');
        }
    } catch (error) {
        console.error('Error loading slots:', error);
        showError('Gagal memuat slot waktu tersedia. Silakan coba lagi.', 'Error');
    } finally {
        loadingSlots.value = false;
    }
};

const nextStep = () => {
    if (canProceed.value && currentStep.value < steps.length - 1) {
        // Extra validation for date step
        if (currentStep.value === 0) {
            const selectedDate = new Date(form.appointment_date);
            const min = new Date(minDate);
            const max = new Date(maxDate);
            
            if (selectedDate < min || selectedDate > max) {
                showError(
                    `Tanggal harus dalam periode minggu ini (${formatDate(minDate)} - ${formatDate(maxDate)})`,
                    'Tanggal Tidak Valid'
                );
                return;
            }
        }
        
        currentStep.value++;
        
        // Load available doctors when entering doctor selection step
        if (currentStep.value === 2) {
            loadingSlots.value = true;
            setTimeout(() => {
                loadingSlots.value = false;
            }, 500);
        }
    }
};

const previousStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
};

const handleSubmit = () => {
    showLoading('Memproses booking Anda...');
    
    // Prepare data to send
    const submitData = {
        doctor_id: form.doctor_id,
        service_ids: form.service_ids,
        appointment_date: form.appointment_date,
        appointment_time: form.appointment_time,
        complaint: form.complaint,
    };

    // Only include pet_id OR pet details, not both
    if (form.pet_id) {
        submitData.pet_id = form.pet_id;
    } else {
        submitData.pet_name = form.pet_name;
        submitData.pet_species = form.pet_species;
        if (form.pet_breed) submitData.pet_breed = form.pet_breed;
        if (form.pet_birth_date) submitData.pet_birth_date = form.pet_birth_date;
        if (form.pet_gender) submitData.pet_gender = form.pet_gender;
        if (form.pet_weight) submitData.pet_weight = form.pet_weight;
    }
    
    router.post('/booking', submitData, {
        onSuccess: () => {
            closeSwal();
            // Redirect will be handled by controller
        },
        onError: (errors) => {
            closeSwal();
            console.error('Booking errors:', errors);
            
            // Show first error message
            const firstError = Object.values(errors)[0];
            showError(firstError || 'Terjadi kesalahan. Silakan periksa form Anda.', 'Booking Gagal');
        }
    });
};

// Watch for changes that require reloading slots
watch(() => [form.doctor_id, form.appointment_date], () => {
    // Reset when dependencies change
    if (currentStep.value >= 2) {
        availableSlots.value = [];
    }
});

// Watch for date or time changes to reset doctor selection
watch(() => [form.appointment_date, form.appointment_time], () => {
    if (form.doctor_id && filteredDoctors.value.length > 0) {
        const doctorStillAvailable = filteredDoctors.value.some(d => d.id === form.doctor_id);
        if (!doctorStillAvailable) {
            form.doctor_id = null;
        }
    }
});

// Watch for date changes to reset doctor selection if selected doctor doesn't practice on new date
watch(() => form.appointment_date, (newDate) => {
    // Validate date is within allowed range
    if (newDate) {
        const selectedDate = new Date(newDate);
        const min = new Date(minDate);
        const max = new Date(maxDate);
        
        if (selectedDate < min || selectedDate > max) {
            form.appointment_date = '';
            showError(
                `Tanggal harus dalam periode minggu ini (${formatDate(minDate)} - ${formatDate(maxDate)})`,
                'Tanggal Tidak Valid'
            );
            return;
        }
    }
    
    if (form.doctor_id && filteredDoctors.value.length > 0) {
        const doctorStillAvailable = filteredDoctors.value.some(d => d.id === form.doctor_id);
        if (!doctorStillAvailable) {
            form.doctor_id = null;
            form.appointment_time = '';
        }
    }
});

// Format date helper
const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
};

// Format pet age helper
const formatPetAge = (birthDate) => {
    if (!birthDate) return 'Umur tidak diketahui';
    
    const birth = new Date(birthDate);
    const now = new Date();
    
    let years = now.getFullYear() - birth.getFullYear();
    let months = now.getMonth() - birth.getMonth();
    
    // Adjust if birth month hasn't occurred yet this year
    if (months < 0) {
        years--;
        months += 12;
    }
    
    // Adjust for day of month
    if (now.getDate() < birth.getDate()) {
        months--;
        if (months < 0) {
            years--;
            months += 12;
        }
    }
    
    if (years > 0 && months > 0) {
        return `${years} tahun ${months} bulan`;
    } else if (years > 0) {
        return `${years} tahun`;
    } else if (months > 0) {
        return `${months} bulan`;
    } else {
        return 'Kurang dari 1 bulan';
    }
};

// Open edit modal with SweetAlert2
const openEditModal = async (pet) => {
    const { value: formValues } = await Swal.fire({
        title: 'Edit Data Hewan',
        html: `
            <div class="text-left space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Hewan *</label>
                    <input id="edit-name" class="swal2-input w-full m-0" value="${pet.name}" placeholder="Nama hewan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Hewan *</label>
                    <select id="edit-species" class="swal2-input w-full m-0">
                        <option value="dog" ${pet.species === 'dog' ? 'selected' : ''}>Anjing</option>
                        <option value="cat" ${pet.species === 'cat' ? 'selected' : ''}>Kucing</option>
                        <option value="bird" ${pet.species === 'bird' ? 'selected' : ''}>Burung</option>
                        <option value="rabbit" ${pet.species === 'rabbit' ? 'selected' : ''}>Kelinci</option>
                        <option value="hamster" ${pet.species === 'hamster' ? 'selected' : ''}>Hamster</option>
                        <option value="other" ${pet.species === 'other' ? 'selected' : ''}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ras/Breed</label>
                    <input id="edit-breed" class="swal2-input w-full m-0" value="${pet.breed || ''}" placeholder="Ras/Breed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                    <select id="edit-gender" class="swal2-input w-full m-0">
                        <option value="">Pilih jenis kelamin</option>
                        <option value="male" ${pet.gender === 'male' ? 'selected' : ''}>Jantan</option>
                        <option value="female" ${pet.gender === 'female' ? 'selected' : ''}>Betina</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input id="edit-birth-date" type="date" class="swal2-input w-full m-0" value="${pet.birth_date || ''}" max="${today}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Berat (kg)</label>
                    <input id="edit-weight" type="number" step="0.1" class="swal2-input w-full m-0" value="${pet.weight || ''}" placeholder="Berat dalam kg">
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3B82F6',
        cancelButtonColor: '#6B7280',
        width: '600px',
        preConfirm: () => {
            const name = document.getElementById('edit-name').value;
            const species = document.getElementById('edit-species').value;
            
            if (!name || !species) {
                Swal.showValidationMessage('Nama dan jenis hewan harus diisi');
                return false;
            }
            
            return {
                name: name,
                species: species,
                breed: document.getElementById('edit-breed').value,
                gender: document.getElementById('edit-gender').value,
                birth_date: document.getElementById('edit-birth-date').value,
                weight: document.getElementById('edit-weight').value,
            };
        }
    });

    if (formValues) {
        showLoading('Menyimpan perubahan...');
        
        router.patch(`/profile/pets/${pet.id}`, formValues, {
            onSuccess: () => {
                closeSwal();
                showSuccess('Data hewan berhasil diperbarui', 'Berhasil');
            },
            onError: (errors) => {
                closeSwal();
                const firstError = Object.values(errors)[0];
                showError(firstError || 'Gagal memperbarui data hewan', 'Error');
            }
        });
    }
};

// Delete pet function
const deletePet = async (pet) => {
    const result = await showConfirm(
        `Apakah Anda yakin ingin menghapus data hewan "${pet.name}"?`,
        'Hapus Data Hewan',
        'Hapus',
        'Batal'
    );
    
    if (result.isConfirmed) {
        showLoading('Menghapus data hewan...');
        
        router.delete(`/profile/pets/${pet.id}`, {
            onSuccess: () => {
                closeSwal();
                showSuccess('Data hewan berhasil dihapus', 'Berhasil');
                
                // Clear selection if deleted pet was selected
                if (form.pet_id === pet.id) {
                    form.pet_id = null;
                }
            },
            onError: (errors) => {
                closeSwal();
                const errorMessage = errors.message || Object.values(errors)[0] || 'Gagal menghapus data hewan';
                showError(errorMessage, 'Error');
            }
        });
    }
};

</script>
