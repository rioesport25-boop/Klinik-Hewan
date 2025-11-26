<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Pet;
use App\Models\Service;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookingController extends Controller
{
    /**
     * Display booking form
     */
    public function index()
    {
        $services = Service::active()->ordered()->get()->map(function ($service) {
            return [
                'id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'icon_url' => $service->icon_path ? asset('storage/' . $service->icon_path) : null,
                'icon_color' => $service->icon_color,
            ];
        });

        // Get current week range (Monday to Sunday)
        $today = \Carbon\Carbon::now('Asia/Jakarta');
        $startOfWeek = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $doctors = Doctor::active()->ordered()->with('schedules')->get()->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'title' => $doctor->title,
                'specialization' => $doctor->specialization,
                'description' => $doctor->description,
                'photo_url' => $doctor->photo_path ? asset('storage/' . $doctor->photo_path) : null,
                'schedules' => $doctor->schedules->map(function ($schedule) {
                    return [
                        'day_of_week' => $schedule->day_of_week,
                        'start_time' => $schedule->start_time->format('H:i'),
                        'end_time' => $schedule->end_time->format('H:i'),
                    ];
                }),
            ];
        });

        // Get user's pets if authenticated
        $userPets = auth()->check() 
            ? auth()->user()->pets()->active()->get()->map(function ($pet) {
                return [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'species' => $pet->species,
                    'species_label' => $pet->species_label,
                    'breed' => $pet->breed,
                    'age_formatted' => $pet->age_formatted,
                    'photo_url' => $pet->photo_url,
                ];
            })
            : collect([]);

        return Inertia::render('Booking/Index', [
            'services' => $services,
            'doctors' => $doctors,
            'userPets' => $userPets,
            'weekStart' => $startOfWeek->format('Y-m-d'),
            'weekEnd' => $endOfWeek->format('Y-m-d'),
            'today' => $today->format('Y-m-d'),
        ]);
    }

    /**
     * Get available time slots for a specific doctor on a specific date
     */
    public function getAvailableSlots(Request $request)
    {
        // Get current week range
        $today = \Carbon\Carbon::now('Asia/Jakarta');
        $startOfWeek = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . $endOfWeek->format('Y-m-d'),
            ],
        ]);

        $date = \Carbon\Carbon::parse($request->date);
        
        // Validate date is within current week
        if ($date->lt($startOfWeek) || $date->gt($endOfWeek)) {
            return response()->json([
                'available' => false,
                'message' => 'Tanggal harus dalam periode minggu ini (' . $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M Y') . ').',
                'slots' => [],
            ], 422);
        }

        $dayOfWeek = strtolower($date->englishDayOfWeek);

        // Check if date is a holiday
        $holiday = Holiday::active()
            ->whereDate('date', $date)
            ->first();

        if ($holiday) {
            return response()->json([
                'available' => false,
                'message' => 'Tanggal yang dipilih adalah hari libur: ' . $holiday->name,
                'slots' => [],
            ]);
        }

        // Get doctor's schedule for this day
        $doctor = Doctor::with(['schedules' => function ($query) use ($dayOfWeek) {
            $query->where('day_of_week', $dayOfWeek)->active();
        }])->findOrFail($request->doctor_id);

        if ($doctor->schedules->isEmpty()) {
            return response()->json([
                'available' => false,
                'message' => 'Dokter tidak praktik pada hari ' . $date->locale('id')->translatedFormat('l'),
                'slots' => [],
            ]);
        }

        $schedule = $doctor->schedules->first();
        $startTime = \Carbon\Carbon::parse($schedule->start_time);
        $endTime = \Carbon\Carbon::parse($schedule->end_time);

        // Get existing appointments on this date
        $existingAppointments = Appointment::where('doctor_id', $request->doctor_id)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->get()
            ->pluck('appointment_time')
            ->map(fn($time) => $time->format('H:i'))
            ->toArray();

        // Generate 30-minute slots
        $slots = [];
        $currentSlot = $startTime->copy();
        
        while ($currentSlot->addMinutes(30) <= $endTime) {
            $slotTime = $currentSlot->format('H:i');
            $slotDateTime = $date->copy()->setTimeFromTimeString($slotTime);
            
            // Skip if slot is in the past (for today)
            if ($date->isToday() && $slotDateTime->isPast()) {
                continue;
            }

            // Check if slot is not booked
            if (!in_array($slotTime, $existingAppointments)) {
                $slots[] = [
                    'time' => $slotTime,
                    'formatted' => $currentSlot->format('H:i'),
                    'available' => true,
                ];
            }
        }

        return response()->json([
            'available' => count($slots) > 0,
            'message' => count($slots) > 0 ? null : 'Tidak ada slot tersedia untuk tanggal ini',
            'slots' => $slots,
            'schedule' => [
                'start_time' => $schedule->start_time->format('H:i'),
                'end_time' => $schedule->end_time->format('H:i'),
            ],
        ]);
    }

    /**
     * Store new appointment
     */
    public function store(Request $request)
    {
        // Get current week range
        $today = \Carbon\Carbon::now('Asia/Jakarta');
        $startOfWeek = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . $endOfWeek->format('Y-m-d'),
                function ($attribute, $value, $fail) use ($startOfWeek, $endOfWeek) {
                    $selectedDate = \Carbon\Carbon::parse($value);
                    if ($selectedDate->lt($startOfWeek) || $selectedDate->gt($endOfWeek)) {
                        $fail('Tanggal harus dalam periode minggu ini (' . $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M Y') . ').');
                    }
                },
            ],
            'appointment_time' => 'required|date_format:H:i',
            'pet_id' => 'nullable|exists:pets,id',
            'pet_name' => 'required_without:pet_id|string|nullable|max:255',
            'pet_species' => 'required_without:pet_id|string|nullable|in:dog,cat,bird,rabbit,hamster,other',
            'pet_breed' => 'nullable|string|max:255',
            'pet_birth_date' => 'nullable|date|before:today',
            'pet_gender' => 'nullable|string|in:male,female',
            'pet_weight' => 'nullable|numeric|min:0',
            'complaint' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Create pet if not exists
            if (!$request->pet_id) {
                $pet = Pet::create([
                    'user_id' => auth()->id(),
                    'name' => $validated['pet_name'],
                    'species' => $validated['pet_species'],
                    'breed' => $validated['pet_breed'] ?? null,
                    'birth_date' => $validated['pet_birth_date'] ?? null,
                    'gender' => $validated['pet_gender'] ?? null,
                    'weight' => $validated['pet_weight'] ?? null,
                    'is_active' => true,
                ]);
                $petId = $pet->id;
            } else {
                $petId = $validated['pet_id'];
            }

            // Create appointment
            $appointmentDateTime = \Carbon\Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);
            $endTime = $appointmentDateTime->copy()->addMinutes(30);

            $appointment = Appointment::create([
                'user_id' => auth()->id(),
                'pet_id' => $petId,
                'doctor_id' => $validated['doctor_id'],
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'end_time' => $endTime->format('H:i:s'),
                'status' => 'pending',
                'complaint' => $validated['complaint'],
            ]);

            // Attach services
            foreach ($validated['service_ids'] as $serviceId) {
                $appointment->services()->attach($serviceId);
            }

            DB::commit();

            return redirect()->route('booking.success', ['bookingCode' => $appointment->booking_code])
                ->with('success', 'Pendaftaran berhasil! Kode booking Anda: ' . $appointment->booking_code);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat membuat appointment: ' . $e->getMessage());
        }
    }

    /**
     * Show booking success page
     */
    public function success($bookingCode)
    {
        $appointment = Appointment::where('booking_code', $bookingCode)
            ->with(['user', 'pet', 'doctor', 'services'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Booking/Success', [
            'appointment' => [
                'booking_code' => $appointment->booking_code,
                'status' => $appointment->status,
                'appointment_date' => $appointment->appointment_date->format('d F Y'),
                'appointment_time' => $appointment->appointment_time->format('H:i'),
                'doctor' => [
                    'name' => $appointment->doctor->name,
                    'title' => $appointment->doctor->title,
                    'photo_url' => $appointment->doctor->photo_path ? asset('storage/' . $appointment->doctor->photo_path) : null,
                ],
                'pet' => [
                    'name' => $appointment->pet->name,
                    'species_label' => $appointment->pet->species_label,
                ],
                'services' => $appointment->services->map(fn($s) => $s->title),
                'complaint' => $appointment->complaint,
            ],
        ]);
    }

    /**
     * Show booking history
     */
    public function history()
    {
        $appointments = Appointment::where('user_id', auth()->id())
            ->with(['pet', 'doctor', 'services'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'booking_code' => $appointment->booking_code,
                    'status' => $appointment->status,
                    'appointment_date' => $appointment->appointment_date->format('d F Y'),
                    'appointment_time' => $appointment->appointment_time->format('H:i'),
                    'doctor' => [
                        'name' => $appointment->doctor->name,
                        'title' => $appointment->doctor->title,
                        'photo_url' => $appointment->doctor->photo_path ? asset('storage/' . $appointment->doctor->photo_path) : null,
                    ],
                    'pet' => [
                        'name' => $appointment->pet->name,
                        'species_label' => $appointment->pet->species_label,
                    ],
                    'services' => $appointment->services->map(fn($s) => $s->title),
                ];
            });

        return Inertia::render('Booking/History', [
            'appointments' => $appointments,
        ]);
    }

    /**
     * Show booking detail
     */
    public function detail($bookingCode)
    {
        $appointment = Appointment::where('booking_code', $bookingCode)
            ->with(['user', 'pet', 'doctor', 'services', 'review'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Booking/Detail', [
            'appointment' => [
                'id' => $appointment->id,
                'booking_code' => $appointment->booking_code,
                'status' => $appointment->status,
                'appointment_date' => $appointment->appointment_date->format('d F Y'),
                'appointment_time' => $appointment->appointment_time->format('H:i'),
                'doctor' => [
                    'name' => $appointment->doctor->name,
                    'title' => $appointment->doctor->title,
                    'photo_url' => $appointment->doctor->photo_path ? asset('storage/' . $appointment->doctor->photo_path) : null,
                ],
                'pet' => [
                    'name' => $appointment->pet->name,
                    'species_label' => $appointment->pet->species_label,
                ],
                'services' => $appointment->services->map(fn($s) => $s->title),
                'complaint' => $appointment->complaint,
                'review' => $appointment->review ? [
                    'rating' => $appointment->review->rating,
                    'comment' => $appointment->review->comment,
                    'created_at' => $appointment->review->created_at->format('d F Y H:i'),
                ] : null,
            ],
        ]);
    }

    /**
     * Cancel appointment
     */
    public function cancel(Appointment $appointment)
    {
        // Ensure user owns this appointment
        if ($appointment->user_id !== auth()->id()) {
            abort(403);
        }

        // Can only cancel pending or confirmed appointments
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return back()->withErrors([
                'message' => 'Hanya booking dengan status menunggu atau dikonfirmasi yang dapat dibatalkan.'
            ]);
        }

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking berhasil dibatalkan');
    }

    /**
     * Submit review for completed appointment
     */
    public function review(Request $request, Appointment $appointment)
    {
        // Ensure user owns this appointment
        if ($appointment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Can only review completed appointments
        if ($appointment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya appointment yang sudah selesai yang dapat diulas.'
            ], 422);
        }

        // Check if already reviewed
        if ($appointment->review()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan ulasan untuk appointment ini.'
            ], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = \App\Models\Review::create([
            'user_id' => auth()->id(),
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_visible' => true,
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas ulasan Anda!',
            'data' => [
                'review_id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment
            ]
        ]);
    }
}
