<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AddressController extends Controller
{
    /**
     * Display user's addresses
     */
    public function index()
    {
        $addresses = auth()->user()->addresses()->latest()->get();

        return Inertia::render('Profile/Addresses/Index', [
            'addresses' => $addresses,
        ]);
    }

    /**
     * Store a new address
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|in:Rumah,Kantor,Lainnya',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'full_address' => 'required|string',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'boolean',
        ]);

        // If this address is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }

        // Create new address
        $address = auth()->user()->addresses()->create($validated);

        return redirect()->route('profile.addresses')->with('success', 'Alamat berhasil ditambahkan!');
    }

    /**
     * Update an address
     */
    public function update(Request $request, Address $address)
    {
        // Check if user owns this address
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'required|in:Rumah,Kantor,Lainnya',
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'full_address' => 'required|string',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'boolean',
        ]);

        // If this address is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            auth()->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return redirect()->route('profile.addresses')->with('success', 'Alamat berhasil diperbarui!');
    }

    /**
     * Delete an address
     */
    public function destroy(Address $address)
    {
        // Check if user owns this address
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->delete();

        return redirect()->route('profile.addresses')->with('success', 'Alamat berhasil dihapus!');
    }

    /**
     * Set an address as default
     */
    public function setDefault(Address $address)
    {
        // Check if user owns this address
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        // Unset all other defaults
        auth()->user()->addresses()->update(['is_default' => false]);

        // Set this address as default
        $address->update(['is_default' => true]);

        return redirect()->route('profile.addresses')->with('success', 'Alamat default berhasil diubah!');
    }
}
