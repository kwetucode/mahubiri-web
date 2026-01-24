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
         @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans leading-relaxed text-[#1b1b18] dark:text-[#EDEDEC] bg-[#FDFDFC] dark:bg-[#0a0a0a] p-8 max-w-4xl mx-auto">
        <a href="{{ url('/') }}" class="inline-block mb-8 px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded no-underline hover:border-[#1915014a] dark:hover:border-[#62605b] text-[#F53003] dark:text-[#FF4433] hover:text-[#d12700] dark:hover:text-[#F61500]">← Retour à l'accueil</a>

        <h1 class="text-5xl font-bold mb-8 text-[#1b1b18] dark:text-[#EDEDEC]">Politique de Confidentialité</h1>

        <p class="text-[#706f6c] dark:text-[#A1A09A] text-sm mb-8">Date de dernière mise à jour : {{ now()->format('d/m/Y') }}</p>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">1. Introduction</h2>
            <p class="mb-4">
                Bienvenue sur {{ config('app.name', 'Mahubiri') }}. Nous attachons une grande importance à la protection de vos données personnelles
                et nous nous engageons à respecter votre vie privée. Cette politique de confidentialité décrit comment nous collectons,
                utilisons, partageons et protégeons vos informations personnelles.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">2. Données collectées</h2>
            <p class="mb-4">Nous collectons différents types de données lorsque vous utilisez notre application :</p>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">2.1 Informations d'inscription</h3>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Nom et prénom</li>
                <li class="mb-2">Adresse e-mail</li>
                <li class="mb-2">Mot de passe (crypté)</li>
                <li class="mb-2">Photo de profil (optionnel)</li>
            </ul>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">2.2 Informations d'utilisation</h3>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Sermons écoutés et téléchargés</li>
                <li class="mb-2">Sermons favoris</li>
                <li class="mb-2">Historique de lecture</li>
                <li class="mb-2">Préférences de notification</li>
            </ul>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">2.3 Données techniques</h3>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Adresse IP</li>
                <li class="mb-2">Type d'appareil</li>
                <li class="mb-2">Système d'exploitation</li>
                <li class="mb-2">Token FCM pour les notifications push</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">3. Utilisation des données</h2>
            <p class="mb-4">Nous utilisons vos données personnelles pour :</p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Créer et gérer votre compte utilisateur</li>
                <li class="mb-2">Vous fournir un accès aux sermons et contenus audio</li>
                <li class="mb-2">Personnaliser votre expérience utilisateur</li>
                <li class="mb-2">Vous envoyer des notifications sur les nouveaux contenus</li>
                <li class="mb-2">Améliorer nos services et fonctionnalités</li>
                <li class="mb-2">Assurer la sécurité de notre plateforme</li>
                <li class="mb-2">Communiquer avec vous concernant votre compte</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">4. Partage des données</h2>
            <p class="mb-4">
                Nous ne vendons pas vos données personnelles. Nous pouvons partager vos informations uniquement dans les cas suivants :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2"><strong>Avec les prédicateurs :</strong> Les informations d'écoute peuvent être partagées avec les prédicateurs pour des statistiques anonymisées</li>
                <li class="mb-2"><strong>Avec les églises :</strong> Si vous êtes membre d'une église enregistrée, certaines données d'utilisation peuvent être partagées</li>
                <li class="mb-2"><strong>Prestataires de services :</strong> Firebase, services d'hébergement, services de stockage cloud</li>
                <li class="mb-2"><strong>Obligations légales :</strong> Si requis par la loi ou pour protéger nos droits légaux</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">5. Sécurité des données</h2>
            <p class="mb-4">
                Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles appropriées pour protéger vos données :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Cryptage des mots de passe avec bcrypt</li>
                <li class="mb-2">Authentification sécurisée avec Laravel Sanctum</li>
                <li class="mb-2">Connexions HTTPS sécurisées</li>
                <li class="mb-2">Accès restreint aux données personnelles</li>
                <li class="mb-2">Surveillance et journalisation des accès</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">6. Vos droits</h2>
            <p class="mb-4">Conformément aux réglementations en vigueur, vous disposez des droits suivants :</p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2"><strong>Droit d'accès :</strong> Vous pouvez demander une copie de vos données personnelles</li>
                <li class="mb-2"><strong>Droit de rectification :</strong> Vous pouvez modifier vos informations personnelles dans votre profil</li>
                <li class="mb-2"><strong>Droit à l'effacement :</strong> Vous pouvez demander la suppression de votre compte et de vos données</li>
                <li class="mb-2"><strong>Droit d'opposition :</strong> Vous pouvez vous opposer au traitement de vos données dans certains cas</li>
                <li class="mb-2"><strong>Droit à la portabilité :</strong> Vous pouvez demander un export de vos données</li>
            </ul>
            <p class="mb-4">
                Pour exercer ces droits, veuillez nous contacter à :
                <a href="mailto:{{ config('mail.from.address', 'support@mahubiri.com') }}" class="text-[#F53003] dark:text-[#FF4433] underline hover:text-[#d12700] dark:hover:text-[#F61500]">{{ config('mail.from.address', 'support@mahubiri.com') }}</a>
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">7. Cookies et technologies similaires</h2>
            <p class="mb-4">
                Notre application utilise des technologies de suivi pour améliorer votre expérience :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Cookies de session pour l'authentification</li>
                <li class="mb-2">Tokens d'authentification pour l'API</li>
                <li class="mb-2">Identifiants d'appareil pour les notifications push</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">8. Conservation des données</h2>
            <p class="mb-4">
                Nous conservons vos données personnelles aussi longtemps que votre compte est actif ou selon les besoins de nos services.
                Les données peuvent être conservées plus longtemps si nécessaire pour se conformer à des obligations légales.
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2"><strong>Données de compte :</strong> Jusqu'à la suppression du compte</li>
                <li class="mb-2"><strong>Historique d'écoute :</strong> 3 ans</li>
                <li class="mb-2"><strong>Logs de sécurité :</strong> 1 an</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">9. Transferts internationaux</h2>
            <p class="mb-4">
                Vos données peuvent être transférées et stockées sur des serveurs situés en dehors de votre pays de résidence.
                Nous prenons les mesures appropriées pour assurer que vos données restent protégées conformément à cette politique.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">10. Services tiers</h2>
            <p class="mb-4">Notre application utilise les services suivants qui peuvent collecter des données :</p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2"><strong>Firebase :</strong> Pour l'authentification et les notifications push</li>
                <li class="mb-2"><strong>OAuth (Facebook, Google) :</strong> Pour l'authentification sociale</li>
                <li class="mb-2"><strong>Services de stockage cloud :</strong> Pour l'hébergement des fichiers audio</li>
            </ul>
            <p class="mb-4">
                Chacun de ces services a sa propre politique de confidentialité que nous vous encourageons à consulter.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">11. Protection des mineurs</h2>
            <p class="mb-4">
                Notre service n'est pas destiné aux personnes de moins de 13 ans. Nous ne collectons pas sciemment
                d'informations personnelles auprès de mineurs. Si vous êtes parent ou tuteur et que vous découvrez
                que votre enfant nous a fourni des données, veuillez nous contacter.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">12. Modifications de cette politique</h2>
            <p class="mb-4">
                Nous pouvons mettre à jour cette politique de confidentialité périodiquement. Nous vous informerons
                de tout changement significatif par e-mail ou via une notification dans l'application.
                La date de "dernière mise à jour" en haut de cette page indique quand la politique a été révisée pour la dernière fois.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">13. Contact</h2>
            <p class="mb-4">
                Pour toute question concernant cette politique de confidentialité ou pour exercer vos droits,
                vous pouvez nous contacter :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2"><strong>Email :</strong> <a href="mailto:{{ config('mail.from.address', 'support@mahubiri.com') }}" class="text-[#F53003] dark:text-[#FF4433] underline hover:text-[#d12700] dark:hover:text-[#F61500]">{{ config('mail.from.address', 'support@mahubiri.com') }}</a></li>
                <li class="mb-2"><strong>Application :</strong> {{ config('app.name', 'Mahubiri') }}</li>
            </ul>
        </section>

        <footer class="mt-12 pt-8 border-t border-[#e3e3e0] dark:border-[#3E3E3A] text-center text-[#706f6c] dark:text-[#A1A09A] text-sm">
            <p>© {{ date('Y') }} {{ config('app.name', 'Mahubiri') }}. Tous droits réservés.</p>
        </footer>
    </body>
</html>
