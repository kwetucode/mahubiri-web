<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Politique de Confidentialité - {{ config('app.name', 'Mahubiri') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                body {
                    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                    line-height: 1.6;
                    color: #1b1b18;
                    background-color: #FDFDFC;
                    padding: 2rem;
                    max-width: 900px;
                    margin: 0 auto;
                }

                @media (prefers-color-scheme: dark) {
                    body {
                        background-color: #0a0a0a;
                        color: #EDEDEC;
                    }

                    a {
                        color: #FF4433;
                    }

                    a:hover {
                        color: #F61500;
                    }
                }

                h1 {
                    font-size: 2.5rem;
                    font-weight: 700;
                    margin-bottom: 2rem;
                    color: #1b1b18;
                }

                @media (prefers-color-scheme: dark) {
                    h1 {
                        color: #EDEDEC;
                    }
                }

                h2 {
                    font-size: 1.75rem;
                    font-weight: 600;
                    margin-top: 2rem;
                    margin-bottom: 1rem;
                    color: #1b1b18;
                }

                @media (prefers-color-scheme: dark) {
                    h2 {
                        color: #EDEDEC;
                    }
                }

                h3 {
                    font-size: 1.25rem;
                    font-weight: 600;
                    margin-top: 1.5rem;
                    margin-bottom: 0.75rem;
                    color: #1b1b18;
                }

                @media (prefers-color-scheme: dark) {
                    h3 {
                        color: #EDEDEC;
                    }
                }

                p {
                    margin-bottom: 1rem;
                }

                ul, ol {
                    margin-bottom: 1rem;
                    padding-left: 2rem;
                }

                li {
                    margin-bottom: 0.5rem;
                }

                a {
                    color: #F53003;
                    text-decoration: underline;
                }

                a:hover {
                    color: #d12700;
                }

                .back-link {
                    display: inline-block;
                    margin-bottom: 2rem;
                    padding: 0.5rem 1rem;
                    border: 1px solid #19140035;
                    border-radius: 0.25rem;
                    text-decoration: none;
                }

                .back-link:hover {
                    border-color: #1915014a;
                }

                @media (prefers-color-scheme: dark) {
                    .back-link {
                        border-color: #3E3E3A;
                    }

                    .back-link:hover {
                        border-color: #62605b;
                    }
                }

                .date {
                    color: #706f6c;
                    font-size: 0.875rem;
                    margin-bottom: 2rem;
                }

                @media (prefers-color-scheme: dark) {
                    .date {
                        color: #A1A09A;
                    }
                }
            </style>
        @endif
    </head>
    <body>
        <a href="{{ url('/') }}" class="back-link">← Retour à l'accueil</a>

        <h1>Politique de Confidentialité</h1>

        <p class="date">Date de dernière mise à jour : {{ now()->format('d/m/Y') }}</p>

        <section>
            <h2>1. Introduction</h2>
            <p>
                Bienvenue sur {{ config('app.name', 'Mahubiri') }}. Nous attachons une grande importance à la protection de vos données personnelles
                et nous nous engageons à respecter votre vie privée. Cette politique de confidentialité décrit comment nous collectons,
                utilisons, partageons et protégeons vos informations personnelles.
            </p>
        </section>

        <section>
            <h2>2. Données collectées</h2>
            <p>Nous collectons différents types de données lorsque vous utilisez notre application :</p>

            <h3>2.1 Informations d'inscription</h3>
            <ul>
                <li>Nom et prénom</li>
                <li>Adresse e-mail</li>
                <li>Mot de passe (crypté)</li>
                <li>Photo de profil (optionnel)</li>
            </ul>

            <h3>2.2 Informations d'utilisation</h3>
            <ul>
                <li>Sermons écoutés et téléchargés</li>
                <li>Sermons favoris</li>
                <li>Historique de lecture</li>
                <li>Préférences de notification</li>
            </ul>

            <h3>2.3 Données techniques</h3>
            <ul>
                <li>Adresse IP</li>
                <li>Type d'appareil</li>
                <li>Système d'exploitation</li>
                <li>Token FCM pour les notifications push</li>
            </ul>
        </section>

        <section>
            <h2>3. Utilisation des données</h2>
            <p>Nous utilisons vos données personnelles pour :</p>
            <ul>
                <li>Créer et gérer votre compte utilisateur</li>
                <li>Vous fournir un accès aux sermons et contenus audio</li>
                <li>Personnaliser votre expérience utilisateur</li>
                <li>Vous envoyer des notifications sur les nouveaux contenus</li>
                <li>Améliorer nos services et fonctionnalités</li>
                <li>Assurer la sécurité de notre plateforme</li>
                <li>Communiquer avec vous concernant votre compte</li>
            </ul>
        </section>

        <section>
            <h2>4. Partage des données</h2>
            <p>
                Nous ne vendons pas vos données personnelles. Nous pouvons partager vos informations uniquement dans les cas suivants :
            </p>
            <ul>
                <li><strong>Avec les prédicateurs :</strong> Les informations d'écoute peuvent être partagées avec les prédicateurs pour des statistiques anonymisées</li>
                <li><strong>Avec les églises :</strong> Si vous êtes membre d'une église enregistrée, certaines données d'utilisation peuvent être partagées</li>
                <li><strong>Prestataires de services :</strong> Firebase, services d'hébergement, services de stockage cloud</li>
                <li><strong>Obligations légales :</strong> Si requis par la loi ou pour protéger nos droits légaux</li>
            </ul>
        </section>

        <section>
            <h2>5. Sécurité des données</h2>
            <p>
                Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles appropriées pour protéger vos données :
            </p>
            <ul>
                <li>Cryptage des mots de passe avec bcrypt</li>
                <li>Authentification sécurisée avec Laravel Sanctum</li>
                <li>Connexions HTTPS sécurisées</li>
                <li>Accès restreint aux données personnelles</li>
                <li>Surveillance et journalisation des accès</li>
            </ul>
        </section>

        <section>
            <h2>6. Vos droits</h2>
            <p>Conformément aux réglementations en vigueur, vous disposez des droits suivants :</p>
            <ul>
                <li><strong>Droit d'accès :</strong> Vous pouvez demander une copie de vos données personnelles</li>
                <li><strong>Droit de rectification :</strong> Vous pouvez modifier vos informations personnelles dans votre profil</li>
                <li><strong>Droit à l'effacement :</strong> Vous pouvez demander la suppression de votre compte et de vos données</li>
                <li><strong>Droit d'opposition :</strong> Vous pouvez vous opposer au traitement de vos données dans certains cas</li>
                <li><strong>Droit à la portabilité :</strong> Vous pouvez demander un export de vos données</li>
            </ul>
            <p>
                Pour exercer ces droits, veuillez nous contacter à :
                <a href="mailto:{{ config('mail.from.address', 'support@mahubiri.com') }}">{{ config('mail.from.address', 'support@mahubiri.com') }}</a>
            </p>
        </section>

        <section>
            <h2>7. Cookies et technologies similaires</h2>
            <p>
                Notre application utilise des technologies de suivi pour améliorer votre expérience :
            </p>
            <ul>
                <li>Cookies de session pour l'authentification</li>
                <li>Tokens d'authentification pour l'API</li>
                <li>Identifiants d'appareil pour les notifications push</li>
            </ul>
        </section>

        <section>
            <h2>8. Conservation des données</h2>
            <p>
                Nous conservons vos données personnelles aussi longtemps que votre compte est actif ou selon les besoins de nos services.
                Les données peuvent être conservées plus longtemps si nécessaire pour se conformer à des obligations légales.
            </p>
            <ul>
                <li><strong>Données de compte :</strong> Jusqu'à la suppression du compte</li>
                <li><strong>Historique d'écoute :</strong> 3 ans</li>
                <li><strong>Logs de sécurité :</strong> 1 an</li>
            </ul>
        </section>

        <section>
            <h2>9. Transferts internationaux</h2>
            <p>
                Vos données peuvent être transférées et stockées sur des serveurs situés en dehors de votre pays de résidence.
                Nous prenons les mesures appropriées pour assurer que vos données restent protégées conformément à cette politique.
            </p>
        </section>

        <section>
            <h2>10. Services tiers</h2>
            <p>Notre application utilise les services suivants qui peuvent collecter des données :</p>
            <ul>
                <li><strong>Firebase :</strong> Pour l'authentification et les notifications push</li>
                <li><strong>OAuth (Facebook, Google) :</strong> Pour l'authentification sociale</li>
                <li><strong>Services de stockage cloud :</strong> Pour l'hébergement des fichiers audio</li>
            </ul>
            <p>
                Chacun de ces services a sa propre politique de confidentialité que nous vous encourageons à consulter.
            </p>
        </section>

        <section>
            <h2>11. Protection des mineurs</h2>
            <p>
                Notre service n'est pas destiné aux personnes de moins de 13 ans. Nous ne collectons pas sciemment
                d'informations personnelles auprès de mineurs. Si vous êtes parent ou tuteur et que vous découvrez
                que votre enfant nous a fourni des données, veuillez nous contacter.
            </p>
        </section>

        <section>
            <h2>12. Modifications de cette politique</h2>
            <p>
                Nous pouvons mettre à jour cette politique de confidentialité périodiquement. Nous vous informerons
                de tout changement significatif par e-mail ou via une notification dans l'application.
                La date de "dernière mise à jour" en haut de cette page indique quand la politique a été révisée pour la dernière fois.
            </p>
        </section>

        <section>
            <h2>13. Contact</h2>
            <p>
                Pour toute question concernant cette politique de confidentialité ou pour exercer vos droits,
                vous pouvez nous contacter :
            </p>
            <ul>
                <li><strong>Email :</strong> <a href="mailto:{{ config('mail.from.address', 'support@mahubiri.com') }}">{{ config('mail.from.address', 'support@mahubiri.com') }}</a></li>
                <li><strong>Application :</strong> {{ config('app.name', 'Mahubiri') }}</li>
            </ul>
        </section>

        <footer style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e3e3e0; text-align: center; color: #706f6c; font-size: 0.875rem;">
            <p>© {{ date('Y') }} {{ config('app.name', 'Mahubiri') }}. Tous droits réservés.</p>
        </footer>
    </body>
</html>
