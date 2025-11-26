<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    /**
     * Update the specified pet.
     */
    public function update(Request $request, Pet $pet)
    {
        // Ensure user owns this pet
        if ($pet->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|in:dog,cat,bird,rabbit,hamster,other',
            'breed' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'gender' => 'nullable|in:male,female',
            'weight' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
        ]);

        $pet->update($validated);

        return back()->with('success', 'Data hewan berhasil diperbarui');
    }

    /**
     * Remove the specified pet.
     */
    public function destroy(Pet $pet)
    {
        // Ensure user owns this pet
        if ($pet->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if pet has any active appointments (not cancelled or completed)
        $activeAppointments = $pet->appointments()
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();

        if ($activeAppointments > 0) {
            return back()->withErrors([
                'message' => 'Tidak dapat menghapus hewan yang memiliki janji temu aktif. Silakan batalkan atau tunggu hingga janji temu selesai.'
            ]);
        }

        // Delete photo if exists
        if ($pet->photo) {
            Storage::disk('public')->delete($pet->photo);
        }

        $pet->delete();

        return back()->with('success', 'Data hewan berhasil dihapus');
    }
}
