<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Conditions d'Utilisation - {{ config('app.name', 'Mahubiri') }}</title>

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

                .warning {
                    background-color: #fff2f2;
                    border-left: 4px solid #F53003;
                    padding: 1rem;
                    margin-bottom: 1rem;
                }

                @media (prefers-color-scheme: dark) {
                    .warning {
                        background-color: #1D0002;
                        border-left-color: #FF4433;
                    }
                }
            </style>
        @endif
    </head>
    <body>
        <a href="{{ url('/') }}" class="back-link">← Retour à l'accueil</a>

        <h1>Conditions d'Utilisation</h1>

        <p class="date">Date de dernière mise à jour : {{ now()->format('d/m/Y') }}</p>

        <div class="warning">
            <strong>Important :</strong> En utilisant {{ config('app.name', 'Mahubiri') }}, vous acceptez ces conditions d'utilisation.
            Veuillez les lire attentivement avant d'utiliser nos services.
        </div>

        <section>
            <h2>1. Acceptation des conditions</h2>
            <p>
                En accédant et en utilisant l'application {{ config('app.name', 'Mahubiri') }} (ci-après "l'Application"),
                vous acceptez d'être lié par ces Conditions d'Utilisation. Si vous n'acceptez pas ces conditions,
                veuillez ne pas utiliser l'Application.
            </p>
        </section>

        <section>
            <h2>2. Description du service</h2>
            <p>
                {{ config('app.name', 'Mahubiri') }} est une plateforme de diffusion de sermons et contenus audio spirituels.
                Notre service permet aux utilisateurs de :
            </p>
            <ul>
                <li>Écouter et télécharger des sermons</li>
                <li>Découvrir des prédicateurs et églises</li>
                <li>Créer et gérer leurs favoris</li>
                <li>Recevoir des notifications sur les nouveaux contenus</li>
                <li>Partager des contenus avec d'autres utilisateurs</li>
            </ul>
        </section>

        <section>
            <h2>3. Création et gestion de compte</h2>

            <h3>3.1 Inscription</h3>
            <p>
                Pour utiliser certaines fonctionnalités de l'Application, vous devez créer un compte. Vous vous engagez à :
            </p>
            <ul>
                <li>Fournir des informations exactes, complètes et à jour</li>
                <li>Maintenir la confidentialité de vos identifiants de connexion</li>
                <li>Être responsable de toutes les activités effectuées via votre compte</li>
                <li>Nous informer immédiatement de toute utilisation non autorisée de votre compte</li>
            </ul>

            <h3>3.2 Âge minimum</h3>
            <p>
                Vous devez avoir au moins 13 ans pour créer un compte. Les mineurs de moins de 18 ans doivent obtenir
                l'autorisation d'un parent ou tuteur légal.
            </p>

            <h3>3.3 Types de comptes</h3>
            <p>L'Application propose différents types de comptes :</p>
            <ul>
                <li><strong>Utilisateur standard :</strong> Accès à l'écoute et au téléchargement de sermons</li>
                <li><strong>Prédicateur :</strong> Possibilité de publier et gérer des sermons</li>
                <li><strong>Administrateur d'église :</strong> Gestion d'une église et de ses prédicateurs</li>
            </ul>
        </section>

        <section>
            <h2>4. Utilisation acceptable</h2>

            <h3>4.1 Vous vous engagez à :</h3>
            <ul>
                <li>Utiliser l'Application conformément aux lois applicables</li>
                <li>Respecter les droits de propriété intellectuelle</li>
                <li>Ne pas utiliser l'Application à des fins commerciales sans autorisation</li>
                <li>Maintenir un comportement respectueux envers les autres utilisateurs</li>
            </ul>

            <h3>4.2 Vous vous engagez à NE PAS :</h3>
            <ul>
                <li>Télécharger ou diffuser du contenu illégal, offensant, diffamatoire ou inapproprié</li>
                <li>Usurper l'identité d'une autre personne ou entité</li>
                <li>Tenter d'accéder de manière non autorisée à nos systèmes ou réseaux</li>
                <li>Utiliser des robots, scrapers ou autres moyens automatisés pour accéder à l'Application</li>
                <li>Perturber ou interférer avec le fonctionnement de l'Application</li>
                <li>Redistribuer le contenu sans autorisation explicite</li>
                <li>Contourner les mesures de sécurité de l'Application</li>
            </ul>
        </section>

        <section>
            <h2>5. Contenu utilisateur</h2>

            <h3>5.1 Propriété du contenu</h3>
            <p>
                Vous conservez tous les droits sur le contenu que vous publiez sur l'Application (sermons, commentaires, etc.).
                Cependant, en publiant du contenu, vous nous accordez une licence mondiale, non exclusive, libre de redevances
                pour utiliser, reproduire, distribuer et afficher ce contenu dans le cadre de nos services.
            </p>

            <h3>5.2 Responsabilité du contenu</h3>
            <p>
                Vous êtes seul responsable du contenu que vous publiez. Nous ne cautionnons ni ne garantissons l'exactitude,
                la qualité ou la fiabilité du contenu publié par les utilisateurs.
            </p>

            <h3>5.3 Modération</h3>
            <p>
                Nous nous réservons le droit, sans obligation, de surveiller, modifier ou supprimer tout contenu que nous jugeons
                inapproprié ou en violation de ces Conditions d'Utilisation.
            </p>
        </section>

        <section>
            <h2>6. Droits de propriété intellectuelle</h2>

            <h3>6.1 Propriété de l'Application</h3>
            <p>
                L'Application, y compris son code source, son design, ses logos et son contenu original, est la propriété de
                {{ config('app.name', 'Mahubiri') }} et est protégée par les lois sur la propriété intellectuelle.
            </p>

            <h3>6.2 Licence d'utilisation</h3>
            <p>
                Nous vous accordons une licence limitée, non exclusive et révocable pour utiliser l'Application à des fins personnelles
                et non commerciales, conformément à ces Conditions d'Utilisation.
            </p>

            <h3>6.3 Contenu des sermons</h3>
            <p>
                Les sermons disponibles sur l'Application appartiennent à leurs auteurs respectifs (prédicateurs ou églises).
                Toute utilisation non autorisée de ce contenu est strictement interdite.
            </p>
        </section>

        <section>
            <h2>7. Abonnements et paiements</h2>
            <p>
                Si nous proposons des fonctionnalités payantes ou des abonnements à l'avenir :
            </p>
            <ul>
                <li>Les prix seront clairement indiqués avant tout achat</li>
                <li>Les paiements sont non remboursables, sauf disposition légale contraire</li>
                <li>Nous nous réservons le droit de modifier les prix avec un préavis raisonnable</li>
                <li>Vous pouvez annuler votre abonnement à tout moment depuis votre compte</li>
            </ul>
        </section>

        <section>
            <h2>8. Disponibilité du service</h2>
            <p>
                Nous nous efforçons de maintenir l'Application disponible 24h/24 et 7j/7, mais nous ne garantissons pas :
            </p>
            <ul>
                <li>Un accès ininterrompu à l'Application</li>
                <li>L'absence d'erreurs ou de bugs</li>
                <li>La disponibilité permanente de tout contenu spécifique</li>
            </ul>
            <p>
                Nous nous réservons le droit de modifier, suspendre ou interrompre tout ou partie de l'Application
                à tout moment, avec ou sans préavis.
            </p>
        </section>

        <section>
            <h2>9. Suspension et résiliation</h2>

            <h3>9.1 Par vous</h3>
            <p>
                Vous pouvez supprimer votre compte à tout moment depuis les paramètres de l'Application.
            </p>

            <h3>9.2 Par nous</h3>
            <p>
                Nous pouvons suspendre ou résilier votre compte si :
            </p>
            <ul>
                <li>Vous violez ces Conditions d'Utilisation</li>
                <li>Vous utilisez l'Application de manière frauduleuse ou abusive</li>
                <li>Nous sommes tenus de le faire par la loi</li>
                <li>Nous cessons de fournir l'Application</li>
            </ul>

            <h3>9.3 Effets de la résiliation</h3>
            <p>
                En cas de résiliation de votre compte :
            </p>
            <ul>
                <li>Vous perdrez l'accès à votre compte et à vos données</li>
                <li>Le contenu que vous avez publié peut être supprimé</li>
                <li>Vos téléchargements et favoris seront perdus</li>
            </ul>
        </section>

        <section>
            <h2>10. Limitation de responsabilité</h2>
            <p>
                Dans les limites autorisées par la loi :
            </p>
            <ul>
                <li>L'Application est fournie "en l'état" sans garantie d'aucune sorte</li>
                <li>Nous ne sommes pas responsables des dommages directs, indirects ou consécutifs résultant de l'utilisation de l'Application</li>
                <li>Nous ne sommes pas responsables du contenu publié par les utilisateurs</li>
                <li>Nous ne garantissons pas l'exactitude ou la fiabilité du contenu</li>
                <li>Notre responsabilité totale ne dépassera pas le montant payé par vous au cours des 12 derniers mois</li>
            </ul>
        </section>

        <section>
            <h2>11. Indemnisation</h2>
            <p>
                Vous acceptez de nous indemniser et de nous défendre contre toute réclamation, perte ou dommage
                résultant de :
            </p>
            <ul>
                <li>Votre utilisation de l'Application</li>
                <li>Votre violation de ces Conditions d'Utilisation</li>
                <li>Votre violation des droits de tiers</li>
                <li>Le contenu que vous publiez sur l'Application</li>
            </ul>
        </section>

        <section>
            <h2>12. Liens vers des sites tiers</h2>
            <p>
                L'Application peut contenir des liens vers des sites web ou services tiers. Nous ne sommes pas responsables
                du contenu, des politiques de confidentialité ou des pratiques de ces sites tiers.
            </p>
        </section>

        <section>
            <h2>13. Modifications des conditions</h2>
            <p>
                Nous nous réservons le droit de modifier ces Conditions d'Utilisation à tout moment. Les modifications importantes
                vous seront notifiées par :
            </p>
            <ul>
                <li>Une notification dans l'Application</li>
                <li>Un e-mail à l'adresse associée à votre compte</li>
                <li>Une mise à jour de la date de "dernière mise à jour" en haut de cette page</li>
            </ul>
            <p>
                Votre utilisation continue de l'Application après de telles modifications constitue votre acceptation
                des nouvelles Conditions d'Utilisation.
            </p>
        </section>

        <section>
            <h2>14. Droit applicable et juridiction</h2>
            <p>
                Ces Conditions d'Utilisation sont régies par les lois en vigueur. Tout litige relatif à ces conditions
                sera soumis à la juridiction compétente.
            </p>
        </section>

        <section>
            <h2>15. Dispositions générales</h2>

            <h3>15.1 Intégralité de l'accord</h3>
            <p>
                Ces Conditions d'Utilisation constituent l'intégralité de l'accord entre vous et nous concernant l'utilisation
                de l'Application.
            </p>

            <h3>15.2 Divisibilité</h3>
            <p>
                Si une disposition de ces conditions est jugée invalide ou inapplicable, les autres dispositions resteront
                en vigueur.
            </p>

            <h3>15.3 Renonciation</h3>
            <p>
                Notre non-exercice d'un droit prévu par ces conditions ne constitue pas une renonciation à ce droit.
            </p>

            <h3>15.4 Cession</h3>
            <p>
                Vous ne pouvez pas céder vos droits ou obligations en vertu de ces conditions sans notre consentement préalable.
                Nous pouvons céder nos droits à tout moment.
            </p>
        </section>

        <section>
            <h2>16. Contact</h2>
            <p>
                Pour toute question concernant ces Conditions d'Utilisation, vous pouvez nous contacter :
            </p>
            <ul>
                <li><strong>Email :</strong> <a href="mailto:{{ config('mail.from.address', 'support@mahubiri.com') }}">{{ config('mail.from.address', 'support@mahubiri.com') }}</a></li>
                <li><strong>Application :</strong> {{ config('app.name', 'Mahubiri') }}</li>
            </ul>
        </section>

        <section>
            <h2>17. Documents connexes</h2>
            <p>
                Ces Conditions d'Utilisation doivent être lues conjointement avec :
            </p>
            <ul>
                <li><a href="{{ route('privacy.policy') }}">Politique de Confidentialité</a></li>
            </ul>
        </section>

        <footer style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e3e3e0; text-align: center; color: #706f6c; font-size: 0.875rem;">
            <p>© {{ date('Y') }} {{ config('app.name', 'Mahubiri') }}. Tous droits réservés.</p>
            <p style="margin-top: 0.5rem;">
                <a href="{{ route('privacy.policy') }}">Politique de Confidentialité</a>
            </p>
        </footer>
    </body>
</html>
