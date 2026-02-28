<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display the list of latest users.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Colonnes autorisées pour le tri
        $allowedSorts = ['name', 'email', 'phone', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $users = User::query()
            ->with('role')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($sortBy === 'role', function ($query) use ($sortDirection) {
                $query->join('roles', 'users.role_id', '=', 'roles.id')
                      ->orderBy('roles.name', $sortDirection)
                      ->select('users.*');
            }, function ($query) use ($sortBy, $sortDirection) {
                if ($sortBy === 'email_verified') {
                    $query->orderByRaw('email_verified_at IS NULL ' . ($sortDirection === 'asc' ? 'DESC' : 'ASC'));
                } else {
                    $query->orderBy($sortBy, $sortDirection);
                }
            })
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role?->name ?? 'Utilisateur',
                'email_verified' => $user->hasVerifiedEmail(),
                'created_at' => $user->created_at->format('d/m/Y H:i'),
                'created_at_human' => $user->created_at->diffForHumans(),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
        ]);
    }
}
