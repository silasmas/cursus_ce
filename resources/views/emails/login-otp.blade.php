<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Code de connexion PHILA-CE</title>
</head>
<body style="margin:0;padding:0;background:#F7F7F8;font-family:Inter,Arial,sans-serif;color:#111827;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:16px;border:1px solid #ECECEE;overflow:hidden;">
          <tr>
            <td style="background:#0A0A0A;padding:28px 32px;text-align:center;">
              <img src="{{ asset('images/phila-logo.png') }}" alt="PHILA" width="64" height="64" style="display:block;margin:0 auto 12px;border-radius:50%;">
              <p style="margin:0;color:#ffffff;font-size:14px;letter-spacing:0.15em;text-transform:uppercase;">PHILA – Cité d'Exaucement</p>
            </td>
          </tr>
          <tr>
            <td style="padding:32px;">
              <h1 style="margin:0 0 12px;font-size:22px;font-weight:700;">Votre code de connexion</h1>
              <p style="margin:0 0 24px;color:#6B7280;line-height:1.6;">
                Utilisez ce code pour accéder à votre espace de formation. Il expire dans {{ $expiryMinutes }} minutes.
              </p>
              <div style="background:#F7F7F8;border:1px solid #ECECEE;border-radius:12px;padding:20px;text-align:center;margin-bottom:24px;">
                <span style="font-size:32px;font-weight:800;letter-spacing:0.35em;color:#0A0A0A;">{{ $code }}</span>
              </div>
              <p style="margin:0;color:#6B7280;font-size:13px;line-height:1.6;">
                Si vous n'avez pas demandé ce code, ignorez cet e-mail.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
