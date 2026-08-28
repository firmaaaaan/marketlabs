<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject ?? 'MarketLabs' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f0fdf4; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-font-smoothing: antialiased;">

    @php
        $address = \App\Models\Setting::get('footer_address', '');
        $phone   = \App\Models\Setting::get('footer_phone', '');
        $email   = \App\Models\Setting::get('footer_email', '');
        $website = config('app.url', 'http://localhost');
    @endphp

    {{-- Header --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #047857;">
        <tr>
            <td style="padding: 28px 24px; text-align: center;">
                <h1 style="margin: 0; padding: 0; font-size: 26px; font-weight: 700; color: #ffffff; letter-spacing: -0.5px;">
                    MarketLabs
                </h1>
                <p style="margin: 4px 0 0 0; padding: 0; font-size: 11px; color: #d1fae5; letter-spacing: 1.5px; text-transform: uppercase;">
                    UPT Laboratorium Terpadu
                </p>
            </td>
        </tr>
    </table>

    {{-- Body --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding: 40px 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; margin: 0 auto;">
                    {{-- Card --}}
                    <tr>
                        <td style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); overflow: hidden;">

                            {{-- Green accent bar --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="height: 4px; background-color: #047857; font-size: 0; line-height: 0;">&nbsp;</td>
                                </tr>
                            </table>

                            {{-- Greeting --}}
                            @if(!empty($greeting))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 32px 32px 0 32px;">
                                        <h2 style="margin: 0; padding: 0; font-size: 22px; font-weight: 600; color: #065f46;">
                                            {{ $greeting }}
                                        </h2>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            {{-- Intro Lines --}}
                            @if(!empty($introLines))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                @foreach($introLines as $line)
                                <tr>
                                    <td style="padding: {{ $loop->first && empty($greeting) ? '32px' : '16px 32px 0 32px' }};">
                                        <p style="margin: 0; padding: 0; font-size: 15px; line-height: 1.7; color: #475569;">
                                            {!! $line !!}
                                        </p>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            @endif

                            {{-- Action Button --}}
                            @if(!empty($actionText) && !empty($actionUrl))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 28px 32px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                            <tr>
                                                <td style="border-radius: 8px; background-color: #047857; box-shadow: 0 2px 8px rgba(4,120,87,0.30);">
                                                    <a href="{{ $actionUrl }}" target="_blank" style="display: inline-block; padding: 14px 36px; font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none; letter-spacing: 0.3px;">
                                                        {{ $actionText }}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            {{-- Additional Actions --}}
                            @if(!empty($actionElements))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                @foreach($actionElements as $element)
                                <tr>
                                    <td style="padding: 0 32px 8px 32px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td>
                                                    <a href="{{ $element['url'] }}" target="_blank" style="font-size: 14px; color: #047857; text-decoration: underline; font-weight: 500;">
                                                        {{ $element['text'] }}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            @endif

                            {{-- Outro Lines --}}
                            @if(!empty($outroLines))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                @foreach($outroLines as $line)
                                <tr>
                                    <td style="padding: {{ $loop->first ? ((!empty($actionText) || !empty($actionElements)) ? '16px 32px 0 32px' : '16px 32px 0 32px') : '8px 32px 0 32px' }};">
                                        <p style="margin: 0; padding: 0; font-size: 15px; line-height: 1.7; color: #475569;">
                                            {!! $line !!}
                                        </p>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            @endif

                            {{-- Salutation --}}
                            @if(!empty($salutation))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 24px 32px 32px 32px;">
                                        <p style="margin: 0; padding: 0; font-size: 14px; line-height: 1.6; color: #6b7280;">
                                            {!! $salutation !!}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 24px 24px 32px 24px; text-align: center;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border-radius: 8px;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <p style="margin: 0; padding: 0; font-size: 13px; font-weight: 600; color: #065f46;">
                                            MarketLabs
                                        </p>
                                        <p style="margin: 2px 0 0 0; padding: 0; font-size: 10px; color: #6b7280; letter-spacing: 0.5px; text-transform: uppercase;">
                                            UPT Laboratorium Terpadu
                                        </p>

                                        {{-- Divider --}}
                                        <table role="presentation" width="60" cellpadding="0" cellspacing="0" style="margin: 14px auto;">
                                            <tr>
                                                <td style="height: 1px; background-color: #d1fae5; font-size: 0; line-height: 0;">&nbsp;</td>
                                            </tr>
                                        </table>

                                        {{-- Contact Info --}}
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                            @if($address)
                                            <tr>
                                                <td style="padding: 3px 0; font-size: 12px; color: #6b7280;">
                                                    &#128205;&nbsp; {{ $address }}
                                                </td>
                                            </tr>
                                            @endif
                                            @if($phone)
                                            <tr>
                                                <td style="padding: 3px 0; font-size: 12px; color: #6b7280;">
                                                    &#128222;&nbsp; {{ $phone }}
                                                </td>
                                            </tr>
                                            @endif
                                            @if($email)
                                            <tr>
                                                <td style="padding: 3px 0; font-size: 12px; color: #6b7280;">
                                                    &#9993;&nbsp; {{ $email }}
                                                </td>
                                            </tr>
                                            @endif
                                            @if($website)
                                            <tr>
                                                <td style="padding: 3px 0; font-size: 12px; color: #6b7280;">
                                                    &#127760;&nbsp; {{ $website }}
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 16px 0 0 0; padding: 0; font-size: 11px; line-height: 1.5; color: #9ca3af;">
                                Email ini dikirim secara otomatis. Mohon untuk tidak membalas email ini.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
