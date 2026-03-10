<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f3f8;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f3f8;padding:40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="padding-bottom:24px;">
                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="background:linear-gradient(135deg,#6B4EAF,#5a3d96);border-radius:14px;padding:10px 14px;">
                                        <img src="{{ asset('logo.png') }}" alt="Mahubiri" width="28" height="28" style="display:block;border-radius:6px;" />
                                    </td>
                                    <td style="padding-left:12px;font-size:22px;font-weight:700;color:#6B4EAF;letter-spacing:-0.3px;">
                                        Mahubiri
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background-color:#ffffff;border-radius:20px;box-shadow:0 4px 24px rgba(107,78,175,0.08);overflow:hidden;">

                            {{-- Purple banner --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="background:linear-gradient(135deg,#6B4EAF 0%,#8B6FCF 100%);padding:32px 36px;">
                                        <p style="margin:0 0 4px;font-size:12px;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,0.7);font-weight:600;">
                                            Nouveau message
                                        </p>
                                        <h1 style="margin:0;font-size:24px;font-weight:700;color:#ffffff;line-height:1.3;">
                                            {{ $contactSubject }}
                                        </h1>
                                    </td>
                                </tr>
                            </table>

                            {{-- Body --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding:32px 36px;">

                                        {{-- Sender info --}}
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:28px;background:#f9f8fc;border-radius:14px;border:1px solid #ede9f5;">
                                            <tr>
                                                <td style="padding:20px 24px;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td style="width:44px;height:44px;background:linear-gradient(135deg,#6B4EAF,#8B6FCF);border-radius:12px;text-align:center;vertical-align:middle;color:#fff;font-size:18px;font-weight:700;">
                                                                {{ strtoupper(mb_substr($contactName, 0, 1)) }}
                                                            </td>
                                                            <td style="padding-left:14px;">
                                                                <p style="margin:0;font-size:16px;font-weight:700;color:#1a1a2e;">{{ $contactName }}</p>
                                                                <p style="margin:2px 0 0;font-size:13px;color:#6b7280;">{{ $contactEmail }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Message --}}
                                        <p style="margin:0 0 8px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;font-weight:600;">
                                            Message
                                        </p>
                                        <div style="font-size:15px;color:#374151;line-height:1.7;white-space:pre-wrap;">{{ $contactMessage }}</div>

                                    </td>
                                </tr>
                            </table>

                            {{-- Reply button --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding:0 36px 32px;" align="center">
                                        <a href="mailto:{{ $contactEmail }}?subject=Re: {{ $contactSubject }}" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#6B4EAF,#5a3d96);color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;border-radius:14px;box-shadow:0 4px 14px rgba(107,78,175,0.3);">
                                            ↩ Répondre à {{ $contactName }}
                                        </a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:28px 20px 0;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.6;">
                                Ce message a été envoyé via le formulaire de contact de
                                <a href="{{ url('/') }}" style="color:#6B4EAF;text-decoration:none;font-weight:600;">mahubiri.tech</a>
                            </p>
                            <p style="margin:8px 0 0;font-size:11px;color:#c4c0cc;">
                                &copy; {{ date('Y') }} Mahubiri. Tous droits réservés.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
