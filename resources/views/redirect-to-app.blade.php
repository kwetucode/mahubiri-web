<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ $appName }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .message {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .loading {
            margin: 20px 0;
        }

        .spinner {
            width: 40px;
            height: 40px;
            margin: 0 auto;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .status-text {
            color: #667eea;
            font-weight: 500;
            margin-top: 10px;
        }

        .app-links {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }

        .app-links p {
            color: #999;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .store-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .store-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: background 0.3s;
        }

        .store-button:hover {
            background: #555;
        }

        .store-button svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        .manual-link {
            margin-top: 20px;
        }

        .manual-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .manual-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 20px;
            }

            .store-buttons {
                flex-direction: column;
            }

            .store-button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path
                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
            </svg>
        </div>

        <h1>{{ $title }}</h1>
        <p class="message">{{ $message }}</p>

        <div class="loading">
            <div class="spinner"></div>
            <p class="status-text">Ouverture de l'application...</p>
        </div>

        <div class="app-links">
            <p>L'application ne s'ouvre pas automatiquement ?</p>

            <div class="store-buttons">
                @if ($playStoreUrl !== '#')
                    <a href="{{ $playStoreUrl }}" class="store-button">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M3 20.5v-17c0-.59.34-1.11.84-1.35L13.69 12l-9.85 9.85c-.5-.24-.84-.76-.84-1.35z" />
                            <path d="M16.81 15.12l-3.12-3.12 3.12-3.12 2.73 1.58c.56.33.56 1.05 0 1.38l-2.73 1.58z" />
                            <path
                                d="M4.06 2.35l9.83 9.65-9.83 9.65c-.07-.03-.14-.07-.19-.12L3 20.5v-17l.87-.03c.05-.05.12-.09.19-.12z" />
                        </svg>
                        Google Play
                    </a>
                @endif

                @if ($appStoreUrl !== '#')
                    <a href="{{ $appStoreUrl }}" class="store-button">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path
                                d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" />
                        </svg>
                        App Store
                    </a>
                @endif
            </div>

            <div class="manual-link">
                <a href="{{ $deepLink }}" id="manualLink">Cliquez ici pour ouvrir manuellement</a>
            </div>
        </div>
    </div>

    <script>
        // Try to open the deep link immediately
        window.location.href = "{{ $deepLink }}";

        // Fallback: try again after a short delay
        setTimeout(function() {
            window.location.href = "{{ $deepLink }}";
        }, 500);

        // Update status text
        setTimeout(function() {
            document.querySelector('.status-text').textContent =
                "Si l'application ne s'ouvre pas, utilisez les liens ci-dessous";
        }, 3000);
    </script>
</body>

</html>
