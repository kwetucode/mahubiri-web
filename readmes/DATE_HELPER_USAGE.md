# DateHelper - Guide d'utilisation

Le `DateHelper` est une classe utilitaire qui permet de formater les dates de manière cohérente et localisée en français dans toute l'application.

## Fonctionnalités principales

### 1. Formatage "il y a" (timeAgo)

Convertit une date en format relatif français :

```php
use App\Helpers\DateHelper;

// Utilisation basique
$date = Carbon::now()->subMinutes(30);
echo DateHelper::timeAgo($date); // "il y a 30 minutes"

// Avec une date de référence personnalisée
$referenceDate = Carbon::now();
echo DateHelper::timeAgo($date, $referenceDate); // "il y a 30 minutes"

// Fonctionne avec les chaînes de caractères
echo DateHelper::timeAgo('2025-01-01 12:00:00'); // "il y a X temps"

// Retourne null pour les valeurs null
echo DateHelper::timeAgo(null); // null
```

#### Formats supportés :

-   **Secondes** : "il y a 1 seconde" / "il y a 30 secondes"
-   **Minutes** : "il y a 1 minute" / "il y a 15 minutes"
-   **Heures** : "il y a 1 heure" / "il y a 5 heures"
-   **Jours** : "il y a 1 jour" / "il y a 10 jours"
-   **Mois** : "il y a 1 mois" / "il y a 6 mois"
-   **Années** : "il y a 1 an" / "il y a 3 ans"

#### Dates futures :

```php
$futureDate = Carbon::now()->addHours(2);
echo DateHelper::timeAgo($futureDate); // "dans 2 heures"
```

### 2. Formatage français standard

```php
use App\Helpers\DateHelper;

$date = Carbon::now();

// Format personnalisé
echo DateHelper::formatFrench($date, 'd/m/Y à H:i'); // "03/10/2025 à 14:30"

// Format par défaut
echo DateHelper::formatFrench($date); // "03/10/2025 à 14:30"
```

### 3. Formatage relatif avancé

```php
use App\Helpers\DateHelper;

$today = Carbon::today()->setTime(10, 30);
$yesterday = Carbon::yesterday()->setTime(15, 45);
$tomorrow = Carbon::tomorrow()->setTime(9, 15);

echo DateHelper::formatRelative($today);     // "aujourd'hui à 10:30"
echo DateHelper::formatRelative($yesterday); // "hier à 15:45"
echo DateHelper::formatRelative($tomorrow);  // "demain à 09:15"

// Pour les autres dates, utilise timeAgo automatiquement
$oldDate = Carbon::now()->subWeek();
echo DateHelper::formatRelative($oldDate);   // "il y a 1 semaine"
```

### 4. Noms français

```php
use App\Helpers\DateHelper;

$date = Carbon::createFromFormat('Y-m-d', '2025-03-15'); // Un samedi

// Nom du jour
echo DateHelper::getDayNameFrench($date); // "samedi"

// Nom du mois
echo DateHelper::getMonthNameFrench($date); // "mars"

// Format complet
echo DateHelper::formatFullFrench($date); // "samedi 15 mars 2025 à 14:30"
```

## Utilisation dans les Resources

### Avant (ChurchResource)

```php
'moment' => $this->created_at
    ? (
        $this->created_at->diffInSeconds(now()) < 60
        ? 'il y a ' . $this->created_at->diffInSeconds(now()) . ' seconde' . ($this->created_at->diffInSeconds(now()) > 1 ? 's' : '')
        : (/* logique complexe imbriquée */)
    )
    : null,
```

### Après (avec DateHelper)

```php
use App\Helpers\DateHelper;

'moment' => DateHelper::timeAgo($this->created_at),
'created_at' => DateHelper::formatFrench($this->created_at, 'd/m/Y H:i:s'),
'created_at_full' => DateHelper::formatFullFrench($this->created_at),
```

## Utilisation dans les Controllers

```php
use App\Helpers\DateHelper;

class SermonController extends Controller
{
    public function index()
    {
        $sermons = Sermon::all();

        foreach ($sermons as $sermon) {
            $sermon->time_ago = DateHelper::timeAgo($sermon->created_at);
            $sermon->formatted_date = DateHelper::formatFullFrench($sermon->created_at);
        }

        return response()->json($sermons);
    }
}
```

## Utilisation dans Blade (si nécessaire)

```blade
@php
    use App\Helpers\DateHelper;
@endphp

<p>Créé {{ DateHelper::timeAgo($church->created_at) }}</p>
<p>Le {{ DateHelper::formatFullFrench($church->created_at) }}</p>
```

## Tests

Le helper est fourni avec une suite complète de tests unitaires dans `tests/Unit/Helpers/DateHelperTest.php`.

```bash
# Exécuter les tests du helper
php artisan test --filter DateHelperTest

# Tests spécifiques
php artisan test --filter DateHelperTest::it_formats_seconds_correctly
```

## Avantages

1. **Réutilisabilité** : Logique centralisée pour toutes les dates
2. **Cohérence** : Format uniforme dans toute l'application
3. **Lisibilité** : Code plus simple et maintenable
4. **Localisation** : Support complet du français avec règles de pluriel
5. **Flexibilité** : Multiple formats et options de personnalisation
6. **Testabilité** : Helper entièrement testé
7. **Performance** : Évite la duplication de logique complexe

## Notes de performance

-   Le helper utilise Carbon qui est optimisé pour les calculs de dates
-   Les méthodes sont statiques pour éviter l'instanciation inutile
-   La logique de formatage est optimisée pour réduire les calculs répétitifs

## Extension future

Le helper peut facilement être étendu pour :

-   Support d'autres langues
-   Formats de dates supplémentaires
-   Intégration avec des libraries de i18n
-   Cache des résultats pour les dates fréquemment utilisées
