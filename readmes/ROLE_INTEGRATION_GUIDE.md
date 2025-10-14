# 🔐 Guide d'Intégration - Système de Rôles avec RoleType Enum

## 📋 Vue d'ensemble

Ce guide explique comment utiliser le système de rôles intégré qui connecte l'enum `RoleType`, le modèle `Role`, et le modèle `User` pour une gestion complète des permissions.

---

## 🏗️ Architecture du Système

### 1. **RoleType Enum** (Type-safe constants)

```php
// app/Enums/RoleType.php
RoleType::USER          // 'user'
RoleType::ADMIN         // 'admin'
RoleType::CHURCH_ADMIN  // 'church_admin'
RoleType::MODERATOR     // 'moderator'
```

### 2. **Role Model** (Database entity)

```php
// app/Models/Role.php
- id, name, slug, description, is_active, timestamps
- slug casté vers RoleType enum
- Méthodes de vérification des privilèges
```

### 3. **User Model** (User entity with role)

```php
// app/Models/User.php
- role_id (foreign key)
- Méthodes de vérification basées sur le rôle
```

---

## 🛠️ Utilisation Pratique

### Créer des Utilisateurs avec Rôles

```php
use App\Models\User;
use App\Models\Role;
use App\Enums\RoleType;

// Récupérer un rôle par type
$adminRole = Role::getByType(RoleType::ADMIN);
$userRole = Role::getByType(RoleType::USER);

// Créer des utilisateurs
$admin = User::create([
    'name' => 'Administrateur',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role_id' => $adminRole->id
]);

$standardUser = User::create([
    'name' => 'Utilisateur Standard',
    'email' => 'user@example.com',
    'password' => bcrypt('password'),
    'role_id' => $userRole->id
]);
```

### Vérifications de Permissions - Niveau User

```php
// Vérifications spécifiques par rôle
if ($user->isAdmin()) {
    // Actions admin uniquement
}

if ($user->isChurchAdmin()) {
    // Actions admin d'église
}

if ($user->isModerator()) {
    // Actions de modération
}

if ($user->isUser()) {
    // Actions utilisateur standard
}

// Vérifications par privilèges
if ($user->hasAdminPrivileges()) {
    // ADMIN ou CHURCH_ADMIN
}

if ($user->canModerate()) {
    // ADMIN ou MODERATOR
}

if ($user->canManageChurch()) {
    // ADMIN ou CHURCH_ADMIN
}

// Vérification générique
if ($user->hasRole(RoleType::ADMIN)) {
    // Vérification directe du rôle
}
```

### Vérifications de Permissions - Niveau Role

```php
$role = Role::getByType(RoleType::CHURCH_ADMIN);

if ($role->hasAdminPrivileges()) {
    // true pour ADMIN et CHURCH_ADMIN
}

if ($role->canModerate()) {
    // true pour ADMIN et MODERATOR
}

if ($role->canManageChurch()) {
    // true pour ADMIN et CHURCH_ADMIN
}

// Vérifier le type
if ($role->isType(RoleType::CHURCH_ADMIN)) {
    // Vérification directe
}
```

---

## 🔍 Requêtes et Filtres

### Filtrer les Rôles

```php
// Rôles actifs uniquement
$activeRoles = Role::active()->get();

// Rôles par type
$adminRoles = Role::ofType(RoleType::ADMIN)->get();
$userRoles = Role::ofType(RoleType::USER)->get();

// Combiner les scopes
$activeAdmins = Role::active()->ofType(RoleType::ADMIN)->get();
```

### Requêtes sur les Utilisateurs

```php
// Utilisateurs avec privilèges admin
$adminUsers = User::whereHas('role', function ($query) {
    $query->whereIn('slug', [RoleType::ADMIN, RoleType::CHURCH_ADMIN]);
})->get();

// Utilisateurs par rôle spécifique
$moderators = User::whereHas('role', function ($query) {
    $query->where('slug', RoleType::MODERATOR);
})->get();

// Avec eager loading
$usersWithRoles = User::with('role')->get();
```

---

## 🎯 Middleware et Autorisations

### Créer un Middleware de Rôles

```php
// app/Http/Middleware/CheckRole.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\RoleType;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!auth()->check() || !auth()->user()->hasRole($role)) {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}
```

### Utilisation dans les Routes

```php
// routes/api.php
Route::middleware(['auth', 'role:' . RoleType::ADMIN])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});

Route::middleware(['auth', 'role:' . RoleType::CHURCH_ADMIN])->group(function () {
    Route::post('/churches/{church}/update', [ChurchController::class, 'update']);
});
```

### Gates et Policies

```php
// app/Providers/AuthServiceProvider.php
use Illuminate\Support\Facades\Gate;
use App\Enums\RoleType;

public function boot()
{
    Gate::define('manage-churches', function ($user) {
        return $user->canManageChurch();
    });

    Gate::define('moderate-content', function ($user) {
        return $user->canModerate();
    });

    Gate::define('admin-access', function ($user) {
        return $user->hasAdminPrivileges();
    });
}

// Utilisation dans les contrôleurs
public function updateChurch(Request $request, Church $church)
{
    $this->authorize('manage-churches');

    // Logique de mise à jour
}
```

---

## 📊 Exemples Concrets d'Utilisation

### Contrôleur avec Autorisations

```php
<?php

namespace App\Http\Controllers;

use App\Models\Church;
use App\Models\User;
use App\Enums\RoleType;
use Illuminate\Http\Request;

class ChurchController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Admin voit toutes les églises
            return Church::all();
        } elseif ($user->isChurchAdmin()) {
            // Church admin voit ses églises
            return Church::where('admin_id', $user->id)->get();
        } else {
            // Utilisateurs standard voient les églises publiques
            return Church::where('is_public', true)->get();
        }
    }

    public function update(Request $request, Church $church)
    {
        $user = auth()->user();

        // Vérifier les permissions
        if (!$user->canManageChurch()) {
            abort(403, 'Permission refusée');
        }

        // Church admin peut modifier seulement ses églises
        if ($user->isChurchAdmin() && $church->admin_id !== $user->id) {
            abort(403, 'Vous ne pouvez modifier que vos propres églises');
        }

        // Admin peut tout modifier

        $church->update($request->validated());
        return $church;
    }
}
```

### Service avec Logique Métier

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Enums\RoleType;

class UserRoleService
{
    public function assignRole(User $user, string $roleType): bool
    {
        $role = Role::getByType($roleType);

        if (!$role || !$role->is_active) {
            return false;
        }

        $user->update(['role_id' => $role->id]);
        return true;
    }

    public function canAssignRole(User $assigner, string $targetRoleType): bool
    {
        // Seuls les admins peuvent assigner des rôles
        if (!$assigner->hasAdminPrivileges()) {
            return false;
        }

        // Church admin ne peut pas créer d'autres admins
        if ($assigner->isChurchAdmin() && in_array($targetRoleType, [
            RoleType::ADMIN,
            RoleType::CHURCH_ADMIN
        ])) {
            return false;
        }

        return true;
    }

    public function getUsersByPrivilege(string $privilege): Collection
    {
        return match($privilege) {
            'admin' => User::whereHas('role', fn($q) =>
                $q->whereIn('slug', [RoleType::ADMIN, RoleType::CHURCH_ADMIN])
            )->get(),

            'moderate' => User::whereHas('role', fn($q) =>
                $q->whereIn('slug', [RoleType::ADMIN, RoleType::MODERATOR])
            )->get(),

            'church_management' => User::whereHas('role', fn($q) =>
                $q->whereIn('slug', [RoleType::ADMIN, RoleType::CHURCH_ADMIN])
            )->get(),

            default => collect()
        };
    }
}
```

---

## 🧪 Tests et Validation

### Seeding pour les Tests

```php
// Dans vos tests
protected function setUp(): void
{
    parent::setUp();
    $this->seed(\Database\Seeders\RoleSeeder::class);
}

// Créer des utilisateurs de test
protected function createUserWithRole(string $roleType): User
{
    $role = Role::getByType($roleType);
    return User::factory()->create(['role_id' => $role->id]);
}
```

### Exemples de Tests

```php
/** @test */
public function admin_can_access_all_resources()
{
    $admin = $this->createUserWithRole(RoleType::ADMIN);

    $this->actingAs($admin)
         ->get('/api/admin/users')
         ->assertStatus(200);
}

/** @test */
public function church_admin_cannot_moderate_content()
{
    $churchAdmin = $this->createUserWithRole(RoleType::CHURCH_ADMIN);

    $this->actingAs($churchAdmin)
         ->post('/api/moderate/content')
         ->assertStatus(403);
}
```

---

## ⚡ Commandes Artisan Utiles

### Seeding des Rôles

```bash
# Créer tous les rôles par défaut
php artisan db:seed --class=RoleSeeder

# Reset et re-seed
php artisan migrate:fresh --seed
```

### Tests

```bash
# Tester les rôles
php artisan test tests/Unit/Models/RoleTest.php
php artisan test tests/Unit/Models/UserRoleTest.php

# Tous les tests
php artisan test --coverage
```

---

## 🔒 Sécurité et Bonnes Pratiques

### 1. **Validation des Rôles**

-   Toujours vérifier que le rôle existe avant assignment
-   Utiliser les méthodes de l'enum pour éviter les erreurs de frappe

### 2. **Permissions Granulaires**

-   Préférer les méthodes de privilèges (`canModerate()`) aux vérifications de rôles directs
-   Permet plus de flexibilité pour l'évolution future

### 3. **Middleware et Gates**

-   Utiliser les middleware pour les vérifications de route
-   Utiliser les Gates pour les vérifications granulaires

### 4. **Cache**

-   Considérer le cache pour les vérifications de permissions fréquentes
-   Invalider le cache lors des changements de rôles

---

## 🚀 Évolutions Futures

### Extensions Possibles

1. **Rôles Hiérarchiques** - Système de hiérarchie entre rôles
2. **Permissions Granulaires** - Table de permissions séparée
3. **Rôles Temporaires** - Rôles avec expiration
4. **Audit Trail** - Historique des changements de rôles
5. **Multi-tenancy** - Rôles par organisation/église

### Structure Suggérée pour Permissions Avancées

```php
// app/Models/Permission.php
class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'description'];
}

// app/Models/Role.php (évolution)
public function permissions()
{
    return $this->belongsToMany(Permission::class);
}
```

---

**✨ Le système de rôles est maintenant complètement intégré et prêt pour la production !**

-   ✅ Enum type-safe pour les constantes de rôles
-   ✅ Modèle Role avec méthodes utilitaires
-   ✅ Modèle User avec vérifications de permissions
-   ✅ Tests complets pour validation
-   ✅ Documentation et exemples d'usage
-   ✅ Migration et seeding automatiques
