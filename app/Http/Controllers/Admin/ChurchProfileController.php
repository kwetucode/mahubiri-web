<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChurchProfileController extends Controller
{
    /**
     * Show the church profile page for the authenticated CHURCH_ADMIN.
     */
    public function show()
    {
        $user = Auth::user();
        $church = $user->church;

        abort_unless($church, 403, 'Aucune église associée à votre compte.');

        return Inertia::render('Admin/ChurchProfile', [
            'church' => [
                'id'             => $church->id,
                'name'           => $church->name,
                'abbreviation'   => $church->abbreviation,
                'visionary_name' => $church->visionary_name,
                'logo_url'       => $church->logo_url ? $this->toAbsoluteUrl($church->logo_url) : null,
                'description'    => $church->description,
                'country_name'   => $church->country_name,
                'country_code'   => $church->country_code,
                'city'           => $church->city,
                'address'        => $church->address,
                'is_active'      => $church->is_active,
                'is_featured'    => $church->is_featured,
                'sermons_count'  => $church->sermons()->count(),
                'created_at'     => $church->created_at?->format('d/m/Y'),
            ],
        ]);
    }

    /**
     * Update the church profile.
     */
    public function update(Request $request, FileUploadService $fileUploadService)
    {
        $user = Auth::user();
        $church = $user->church;

        abort_unless($church, 403, 'Aucune église associée à votre compte.');

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'abbreviation'   => ['nullable', 'string', 'max:50'],
            'visionary_name' => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'country_name'   => ['nullable', 'string', 'max:100'],
            'city'           => ['nullable', 'string', 'max:100'],
            'address'        => ['nullable', 'string', 'max:500'],
            'logo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo'    => ['nullable', 'boolean'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($church->logo_url) {
                $fileUploadService->deleteFile($church->logo_url, 'image');
            }
            $validated['logo_url'] = $fileUploadService->handleImageUpload($request->file('logo'), 'logos');
        } elseif ($request->boolean('remove_logo') && $church->logo_url) {
            $fileUploadService->deleteFile($church->logo_url, 'image');
            $validated['logo_url'] = null;
        }

        // Remove non-model fields
        unset($validated['logo'], $validated['remove_logo']);

        $church->update($validated);

        return redirect()->route('admin.church-profile')
            ->with('success', 'Informations de l\'église mises à jour avec succès.');
    }

    /**
     * Convert a relative storage URL to an absolute URL.
     */
    private function toAbsoluteUrl(?string $url): ?string
    {
        if (!$url) return null;
        if (str_starts_with($url, 'http')) return $url;
        return url($url);
    }
}
