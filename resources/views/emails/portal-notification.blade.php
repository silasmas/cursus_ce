<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#F7F7F8;font-family:Inter,Arial,sans-serif;color:#111827;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:16px;border:1px solid #ECECEE;overflow:hidden;">
          <tr>
            <td style="background:#0A0A0A;padding:28px 32px;text-align:center;">
              <img src="{{ asset('images/phila-logo.png') }}" alt="PHILA" width="64" height="64" style="display:block;margin:0 auto 12px;border-radius:50%;">
              <p style="margin:0;color:#ffffff;font-size:14px;letter-spacing:0.15em;text-transform:uppercase;">PHILA – Cité d'Exaucement</p>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <h1 style="margin:0 0 12px;font-size:20px;font-weight:700;">{{ $title }}</h1>
              <p style="margin:0 0 24px;color:#6B7280;line-height:1.6;">{!! nl2br(e($body)) !!}</p>
              @if($actionUrl)
                <p style="margin:0 0 24px;">
                  <a href="{{ $actionUrl }}" style="display:inline-block;background:#E85D04;color:#ffffff;text-decoration:none;font-weight:600;padding:12px 24px;border-radius:10px;">
                    {{ $actionLabel ?? 'Ouvrir' }}
                  </a>
                </p>
              @endif
              <p style="margin:0;color:#9CA3AF;font-size:12px;line-height:1.5;">
                Vous recevez cet e-mail car une action a été effectuée sur votre espace PHILA-CE.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
