<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Conditions d'Utilisation - {{ config('app.name', 'Mahubiri') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

         @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans leading-relaxed text-[#1b1b18] dark:text-[#EDEDEC] bg-[#FDFDFC] dark:bg-[#0a0a0a] p-8 max-w-4xl mx-auto">
        <a href="{{ url('/') }}" class="inline-block mb-8 px-4 py-2 border border-[#19140035] dark:border-[#3E3E3A] rounded no-underline hover:border-[#1915014a] dark:hover:border-[#62605b] text-[#F53003] dark:text-[#FF4433] hover:text-[#d12700] dark:hover:text-[#F61500]">← Retour à l'accueil</a>

        <h1 class="text-5xl font-bold mb-8 text-[#1b1b18] dark:text-[#EDEDEC]">Conditions d'Utilisation</h1>

        <p class="text-[#706f6c] dark:text-[#A1A09A] text-sm mb-8">Date de dernière mise à jour : {{ now()->format('d/m/Y') }}</p>

        <div class="bg-[#fff2f2] dark:bg-[#1D0002] border-l-4 border-[#F53003] dark:border-[#FF4433] p-4 mb-4">
            <strong>Important :</strong> En utilisant {{ config('app.name', 'Mahubiri') }}, vous acceptez ces conditions d'utilisation.
            Veuillez les lire attentivement avant d'utiliser nos services.
        </div>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">1. Acceptation des conditions</h2>
            <p class="mb-4">
                En accédant et en utilisant l'application {{ config('app.name', 'Mahubiri') }} (ci-après "l'Application"),
                vous acceptez d'être lié par ces Conditions d'Utilisation. Si vous n'acceptez pas ces conditions,
                veuillez ne pas utiliser l'Application.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">2. Description du service</h2>
            <p class="mb-4">
                {{ config('app.name', 'Mahubiri') }} est une plateforme de diffusion de sermons et contenus audio spirituels.
                Notre service permet aux utilisateurs de :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Écouter et télécharger des sermons</li>
                <li class="mb-2">Découvrir des prédicateurs et églises</li>
                <li class="mb-2">Créer et gérer leurs favoris</li>
                <li class="mb-2">Recevoir des notifications sur les nouveaux contenus</li>
                <li class="mb-2">Partager des contenus avec d'autres utilisateurs</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">3. Création et gestion de compte</h2>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">3.1 Inscription</h3>
            <p class="mb-4">
                Pour utiliser certaines fonctionnalités de l'Application, vous devez créer un compte. Vous vous engagez à :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Fournir des informations exactes, complètes et à jour</li>
                <li class="mb-2">Maintenir la confidentialité de vos identifiants de connexion</li>
                <li class="mb-2">Être responsable de toutes les activités effectuées via votre compte</li>
                <li class="mb-2">Nous informer immédiatement de toute utilisation non autorisée de votre compte</li>
            </ul>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">3.2 Âge minimum</h3>
            <p class="mb-4">
                Vous devez avoir au moins 13 ans pour créer un compte. Les mineurs de moins de 18 ans doivent obtenir
                l'autorisation d'un parent ou tuteur légal.
            </p>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">3.3 Types de comptes</h3>
            <p class="mb-4">L'Application propose différents types de comptes :</p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2"><strong>Utilisateur standard :</strong> Accès à l'écoute et au téléchargement de sermons</li>
                <li class="mb-2"><strong>Prédicateur :</strong> Possibilité de publier et gérer des sermons</li>
                <li class="mb-2"><strong>Administrateur d'église :</strong> Gestion d'une église et de ses prédicateurs</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">4. Utilisation acceptable</h2>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">4.1 Vous vous engagez à :</h3>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Utiliser l'Application conformément aux lois applicables</li>
                <li class="mb-2">Respecter les droits de propriété intellectuelle</li>
                <li class="mb-2">Ne pas utiliser l'Application à des fins commerciales sans autorisation</li>
                <li class="mb-2">Maintenir un comportement respectueux envers les autres utilisateurs</li>
            </ul>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">4.2 Vous vous engagez à NE PAS :</h3>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Télécharger ou diffuser du contenu illégal, offensant, diffamatoire ou inapproprié</li>
                <li class="mb-2">Usurper l'identité d'une autre personne ou entité</li>
                <li class="mb-2">Tenter d'accéder de manière non autorisée à nos systèmes ou réseaux</li>
                <li class="mb-2">Utiliser des robots, scrapers ou autres moyens automatisés pour accéder à l'Application</li>
                <li class="mb-2">Perturber ou interférer avec le fonctionnement de l'Application</li>
                <li class="mb-2">Redistribuer le contenu sans autorisation explicite</li>
                <li class="mb-2">Contourner les mesures de sécurité de l'Application</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">5. Contenu utilisateur</h2>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">5.1 Propriété du contenu</h3>
            <p class="mb-4">
                Vous conservez tous les droits sur le contenu que vous publiez sur l'Application (sermons, commentaires, etc.).
                Cependant, en publiant du contenu, vous nous accordez une licence mondiale, non exclusive, libre de redevances
                pour utiliser, reproduire, distribuer et afficher ce contenu dans le cadre de nos services.
            </p>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">5.2 Responsabilité du contenu</h3>
            <p class="mb-4">
                Vous êtes seul responsable du contenu que vous publiez. Nous ne cautionnons ni ne garantissons l'exactitude,
                la qualité ou la fiabilité du contenu publié par les utilisateurs.
            </p>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">5.3 Modération</h3>
            <p class="mb-4">
                Nous nous réservons le droit, sans obligation, de surveiller, modifier ou supprimer tout contenu que nous jugeons
                inapproprié ou en violation de ces Conditions d'Utilisation.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">6. Droits de propriété intellectuelle</h2>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">6.1 Propriété de l'Application</h3>
            <p class="mb-4">
                L'Application, y compris son code source, son design, ses logos et son contenu original, est la propriété de
                {{ config('app.name', 'Mahubiri') }} et est protégée par les lois sur la propriété intellectuelle.
            </p>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">6.2 Licence d'utilisation</h3>
            <p class="mb-4">
                Nous vous accordons une licence limitée, non exclusive et révocable pour utiliser l'Application à des fins personnelles
                et non commerciales, conformément à ces Conditions d'Utilisation.
            </p>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">6.3 Contenu des sermons</h3>
            <p class="mb-4">
                Les sermons disponibles sur l'Application appartiennent à leurs auteurs respectifs (prédicateurs ou églises).
                Toute utilisation non autorisée de ce contenu est strictement interdite.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">7. Abonnements et paiements</h2>
            <p class="mb-4">
                Si nous proposons des fonctionnalités payantes ou des abonnements à l'avenir :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Les prix seront clairement indiqués avant tout achat</li>
                <li class="mb-2">Les paiements sont non remboursables, sauf disposition légale contraire</li>
                <li class="mb-2">Nous nous réservons le droit de modifier les prix avec un préavis raisonnable</li>
                <li class="mb-2">Vous pouvez annuler votre abonnement à tout moment depuis votre compte</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">8. Disponibilité du service</h2>
            <p class="mb-4">
                Nous nous efforçons de maintenir l'Application disponible 24h/24 et 7j/7, mais nous ne garantissons pas :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Un accès ininterrompu à l'Application</li>
                <li class="mb-2">L'absence d'erreurs ou de bugs</li>
                <li class="mb-2">La disponibilité permanente de tout contenu spécifique</li>
            </ul>
            <p class="mb-4">
                Nous nous réservons le droit de modifier, suspendre ou interrompre tout ou partie de l'Application
                à tout moment, avec ou sans préavis.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">9. Suspension et résiliation</h2>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">9.1 Par vous</h3>
            <p class="mb-4">
                Vous pouvez supprimer votre compte à tout moment depuis les paramètres de l'Application.
            </p>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">9.2 Par nous</h3>
            <p class="mb-4">
                Nous pouvons suspendre ou résilier votre compte si :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Vous violez ces Conditions d'Utilisation</li>
                <li class="mb-2">Vous utilisez l'Application de manière frauduleuse ou abusive</li>
                <li class="mb-2">Nous sommes tenus de le faire par la loi</li>
                <li class="mb-2">Nous cessons de fournir l'Application</li>
            </ul>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">9.3 Effets de la résiliation</h3>
            <p class="mb-4">
                En cas de résiliation de votre compte :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Vous perdrez l'accès à votre compte et à vos données</li>
                <li class="mb-2">Le contenu que vous avez publié peut être supprimé</li>
                <li class="mb-2">Vos téléchargements et favoris seront perdus</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">10. Limitation de responsabilité</h2>
            <p class="mb-4">
                Dans les limites autorisées par la loi :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">L'Application est fournie "en l'état" sans garantie d'aucune sorte</li>
                <li class="mb-2">Nous ne sommes pas responsables des dommages directs, indirects ou consécutifs résultant de l'utilisation de l'Application</li>
                <li class="mb-2">Nous ne sommes pas responsables du contenu publié par les utilisateurs</li>
                <li class="mb-2">Nous ne garantissons pas l'exactitude ou la fiabilité du contenu</li>
                <li class="mb-2">Notre responsabilité totale ne dépassera pas le montant payé par vous au cours des 12 derniers mois</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">11. Indemnisation</h2>
            <p class="mb-4">
                Vous acceptez de nous indemniser et de nous défendre contre toute réclamation, perte ou dommage
                résultant de :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Votre utilisation de l'Application</li>
                <li class="mb-2">Votre violation de ces Conditions d'Utilisation</li>
                <li class="mb-2">Votre violation des droits de tiers</li>
                <li class="mb-2">Le contenu que vous publiez sur l'Application</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">12. Liens vers des sites tiers</h2>
            <p class="mb-4">
                L'Application peut contenir des liens vers des sites web ou services tiers. Nous ne sommes pas responsables
                du contenu, des politiques de confidentialité ou des pratiques de ces sites tiers.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">13. Modifications des conditions</h2>
            <p class="mb-4">
                Nous nous réservons le droit de modifier ces Conditions d'Utilisation à tout moment. Les modifications importantes
                vous seront notifiées par :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2">Une notification dans l'Application</li>
                <li class="mb-2">Un e-mail à l'adresse associée à votre compte</li>
                <li class="mb-2">Une mise à jour de la date de "dernière mise à jour" en haut de cette page</li>
            </ul>
            <p class="mb-4">
                Votre utilisation continue de l'Application après de telles modifications constitue votre acceptation
                des nouvelles Conditions d'Utilisation.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">14. Droit applicable et juridiction</h2>
            <p class="mb-4">
                Ces Conditions d'Utilisation sont régies par les lois en vigueur. Tout litige relatif à ces conditions
                sera soumis à la juridiction compétente.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">15. Dispositions générales</h2>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">15.1 Intégralité de l'accord</h3>
            <p class="mb-4">
                Ces Conditions d'Utilisation constituent l'intégralité de l'accord entre vous et nous concernant l'utilisation
                de l'Application.
            </p>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">15.2 Divisibilité</h3>
            <p class="mb-4">
                Si une disposition de ces conditions est jugée invalide ou inapplicable, les autres dispositions resteront
                en vigueur.
            </p>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">15.3 Renonciation</h3>
            <p class="mb-4">
                Notre non-exercice d'un droit prévu par ces conditions ne constitue pas une renonciation à ce droit.
            </p>

            <h3 class="text-xl font-semibold mt-6 mb-3 text-[#1b1b18] dark:text-[#EDEDEC]">15.4 Cession</h3>
            <p class="mb-4">
                Vous ne pouvez pas céder vos droits ou obligations en vertu de ces conditions sans notre consentement préalable.
                Nous pouvons céder nos droits à tout moment.
            </p>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">16. Contact</h2>
            <p class="mb-4">
                Pour toute question concernant ces Conditions d'Utilisation, vous pouvez nous contacter :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2"><strong>Email :</strong> <a href="mailto:{{ config('mail.from.address', 'support@mahubiri.com') }}" class="text-[#F53003] dark:text-[#FF4433] underline hover:text-[#d12700] dark:hover:text-[#F61500]">{{ config('mail.from.address', 'support@mahubiri.com') }}</a></li>
                <li class="mb-2"><strong>Application :</strong> {{ config('app.name', 'Mahubiri') }}</li>
            </ul>
        </section>

        <section>
            <h2 class="text-3xl font-semibold mt-8 mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">17. Documents connexes</h2>
            <p class="mb-4">
                Ces Conditions d'Utilisation doivent être lues conjointement avec :
            </p>
            <ul class="mb-4 pl-8 list-disc">
                <li class="mb-2"><a href="{{ route('privacy.policy') }}" class="text-[#F53003] dark:text-[#FF4433] underline hover:text-[#d12700] dark:hover:text-[#F61500]">Politique de Confidentialité</a></li>
            </ul>
        </section>

        <footer class="mt-12 pt-8 border-t border-[#e3e3e0] dark:border-[#3E3E3A] text-center text-[#706f6c] dark:text-[#A1A09A] text-sm">
            <p class="mb-4">© {{ date('Y') }} {{ config('app.name', 'Mahubiri') }}. Tous droits réservés.</p>
            <p class="mb-4">
                <a href="{{ route('privacy.policy') }}" class="text-[#F53003] dark:text-[#FF4433] underline hover:text-[#d12700] dark:hover:text-[#F61500]">Politique de Confidentialité</a>
            </p>
        </footer>
    </body>
</html>
