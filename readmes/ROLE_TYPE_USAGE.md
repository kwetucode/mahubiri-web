# RoleType Enum - Guide d'utilisation

L'enum `RoleType` définit les différents rôles utilisateur dans l'application avec leurs privilèges et méthodes utilitaires.

## Rôles disponibles

| Constante      | Valeur | Description             | Français                |
| -------------- | ------ | ----------------------- | ----------------------- |
| `USER`         | 0      | Utilisateur standard    | Utilisateur             |
| `ADMIN`        | 1      | Administrateur système  | Administrateur          |
| `CHURCH_ADMIN` | 2      | Administrateur d'église | Administrateur d'église |
| `MODERATOR`    | 3      | Modérateur de contenu   | Modérateur              |

## Utilisation de base

```php
use App\Enums\RoleType;

// Accès aux constantes
$userRole = RoleType::USER;           // 0
$adminRole = RoleType::ADMIN;         // 1
$churchAdminRole = RoleType::CHURCH_ADMIN; // 2
$moderatorRole = RoleType::MODERATOR; // 3

// Méthodes statiques
$admin = RoleType::ADMIN();
$user = RoleType::USER();
```

## Méthodes utilitaires

### Obtenir la description en français

```php
use App\Enums\RoleType;

echo RoleType::getDescription(RoleType::USER);        // "Utilisateur"
echo RoleType::getDescription(RoleType::ADMIN);       // "Administrateur"
echo RoleType::getDescription(RoleType::CHURCH_ADMIN); // "Administrateur d'église"
echo RoleType::getDescription(RoleType::MODERATOR);   // "Modérateur"
```

### Obtenir tous les rôles avec descriptions

```php
use App\Enums\RoleType;

$roles = RoleType::getRolesWithDescriptions();
/*
Retourne :
[
    0 => "Utilisateur",
    1 => "Administrateur",
    2 => "Administrateur d'église",
    3 => "Modérateur"
]
*/

// Utilisation dans un select HTML
foreach ($roles as $value => $description) {
    echo "<option value='{$value}'>{$description}</option>";
}
```

## Méthodes de vérification des privilèges

### Vérifier les privilèges d'administration

```php
use App\Enums\RoleType;

// Vérifie si le rôle a des privilèges d'admin (ADMIN ou CHURCH_ADMIN)
RoleType::hasAdminPrivileges(RoleType::ADMIN);       // true
RoleType::hasAdminPrivileges(RoleType::CHURCH_ADMIN); // true
RoleType::hasAdminPrivileges(RoleType::USER);        // false
RoleType::hasAdminPrivileges(RoleType::MODERATOR);   // false
```

### Vérifier les privilèges de modération

```php
use App\Enums\RoleType;

// Vérifie si le rôle peut modérer du contenu (ADMIN ou MODERATOR)
RoleType::canModerate(RoleType::ADMIN);     // true
RoleType::canModerate(RoleType::MODERATOR); // true
RoleType::canModerate(RoleType::USER);      // false
RoleType::canModerate(RoleType::CHURCH_ADMIN); // false
```

### Vérifier les privilèges de gestion d'église

```php
use App\Enums\RoleType;

// Vérifie si le rôle peut gérer une église (ADMIN ou CHURCH_ADMIN)
RoleType::canManageChurch(RoleType::ADMIN);       // true
RoleType::canManageChurch(RoleType::CHURCH_ADMIN); // true
RoleType::canManageChurch(RoleType::USER);        // false
RoleType::canManageChurch(RoleType::MODERATOR);   // false
```

## Utilisation dans les modèles

### Modèle User avec RoleType

```php
use App\Enums\RoleType;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'role_id'];

    /**
     * Vérifier si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return RoleType::hasAdminPrivileges($this->role_id);
    }

    /**
     * Vérifier si l'utilisateur peut modérer
     */
    public function canModerate(): bool
    {
        return RoleType::canModerate($this->role_id);
    }

    /**
     * Vérifier si l'utilisateur peut gérer une église
     */
    public function canManageChurch(): bool
    {
        return RoleType::canManageChurch($this->role_id);
    }

    /**
     * Obtenir la description du rôle
     */
    public function getRoleDescriptionAttribute(): string
    {
        return RoleType::getDescription($this->role_id);
    }
}
```

## Utilisation dans les Controllers

### Middleware de vérification des rôles

```php
use App\Enums\RoleType;

class ChurchController extends Controller
{
    public function store(Request $request)
    {
        // Vérifier si l'utilisateur peut créer une église
        if (!RoleType::canManageChurch(auth()->user()->role_id)) {
            return response()->json([
                'error' => 'Vous n\'avez pas les privilèges pour créer une église'
            ], 403);
        }

        // Logique de création...
    }
}
```

### Controller d'administration

```php
use App\Enums\RoleType;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!RoleType::hasAdminPrivileges(auth()->user()->role_id)) {
                abort(403, 'Accès refusé : privilèges administrateur requis');
            }
            return $next($request);
        });
    }
}
```

## Utilisation dans les Resources

```php
use App\Enums\RoleType;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => [
                'id' => $this->role_id,
                'name' => RoleType::getDescription($this->role_id),
                'can_moderate' => RoleType::canModerate($this->role_id),
                'can_manage_church' => RoleType::canManageChurch($this->role_id),
                'has_admin_privileges' => RoleType::hasAdminPrivileges($this->role_id),
            ],
        ];
    }
}
```

## Utilisation dans les Policies

```php
use App\Enums\RoleType;

class ChurchPolicy
{
    public function create(User $user): bool
    {
        return RoleType::canManageChurch($user->role_id);
    }

    public function update(User $user, Church $church): bool
    {
        // Admin peut tout modifier, church_admin seulement sa propre église
        return RoleType::hasAdminPrivileges($user->role_id) ||
               ($user->role_id === RoleType::CHURCH_ADMIN && $church->created_by === $user->id);
    }

    public function delete(User $user, Church $church): bool
    {
        // Seuls les admins peuvent supprimer
        return $user->role_id === RoleType::ADMIN;
    }
}
```

## Seeders et Factories

### Seeder pour les rôles

```php
use App\Enums\RoleType;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => RoleType::USER, 'name' => 'user'],
            ['id' => RoleType::ADMIN, 'name' => 'admin'],
            ['id' => RoleType::CHURCH_ADMIN, 'name' => 'church_admin'],
            ['id' => RoleType::MODERATOR, 'name' => 'moderator'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['id' => $role['id']], $role);
        }
    }
}
```

### Factory avec rôles

```php
use App\Enums\RoleType;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'role_id' => $this->faker->randomElement([
                RoleType::USER,
                RoleType::MODERATOR,
                RoleType::CHURCH_ADMIN,
            ]),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => RoleType::ADMIN,
        ]);
    }

    public function churchAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => RoleType::CHURCH_ADMIN,
        ]);
    }
}
```

## Avantages de cette implémentation

1. **Type-safe** : Utilisation d'enum plutôt que de chaînes magiques
2. **Centralisé** : Toute la logique des rôles dans une seule classe
3. **Extensible** : Facile d'ajouter de nouveaux rôles ou privilèges
4. **Lisible** : Méthodes expressives pour vérifier les permissions
5. **Maintenant** : Changements de logique centralisés
6. **Testé** : Facile à tester avec des valeurs constantes
