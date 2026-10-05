<!DOCTYPE html>
<html lang="bs">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="dark">
  <meta name="supported-color-schemes" content="dark">
  <title>Dobrodošli u snovi.fm</title>
</head>
<body style="margin:0;padding:0;background:#05060f;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#e2e8f0;">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Vaš kod za aktivaciju: {{ $codeGroups }}. Otvorite link i laku noć počinje večeras.</div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#05060f;">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

          {{-- Logo --}}
          <tr>
            <td align="center" style="padding:8px 0 28px;">
              <img src="https://snovi.fm/logo.png" width="120" height="120" alt="snovi.fm" style="display:block;border:0;width:120px;height:120px;">
            </td>
          </tr>

          {{-- Hero --}}
          <tr>
            <td style="background:#0b1026;background-image:linear-gradient(160deg,#1e1b4b 0%,#0b1026 55%,#05060f 100%);border:1px solid #23264a;border-radius:28px;padding:40px 32px;text-align:center;">
              <p style="margin:0 0 14px;font-size:11px;font-weight:800;letter-spacing:4px;text-transform:uppercase;color:#a78bfa;">✨ Dobrodošli u snovi.fm premium</p>
              <h1 style="margin:0;font-family:Georgia,'Times New Roman',serif;font-size:36px;line-height:1.1;font-weight:700;color:#ffffff;">Laku noć počinje večeras 🌙</h1>
              <p style="margin:18px 0 0;font-size:16px;line-height:26px;color:#cbd5e1;">
                Hvala na pretplati! Vaš <b style="color:#ffffff;">{{ $planLabel }}</b> je aktivan{{ $expiresAt ? ' do '.$expiresAt : '' }}.
                Ostalo je samo da ga aktivirate u aplikaciji.
              </p>

              {{-- Voucher --}}
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:32px 0 0;">
                <tr>
                  <td style="background:#ffffff;border-radius:22px;padding:26px 20px;text-align:center;">
                    <p style="margin:0;font-size:11px;font-weight:800;letter-spacing:3px;text-transform:uppercase;color:#7c3aed;">Vaš kod za aktivaciju</p>
                    <p style="margin:12px 0 0;font-family:'SFMono-Regular',Menlo,Consolas,monospace;font-size:30px;font-weight:800;letter-spacing:5px;color:#1e1b4b;">{{ $codeGroups }}</p>
                    <p style="margin:10px 0 0;font-size:12px;color:#64748b;">Sačuvajte ovaj email, kod vam treba ako promijenite telefon.</p>
                  </td>
                </tr>
              </table>

              {{-- CTA --}}
              <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:28px auto 0;">
                <tr>
                  <td style="border-radius:16px;background:#7c3aed;">
                    <a href="{{ $link }}" style="display:inline-block;padding:18px 34px;font-size:15px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;color:#ffffff;text-decoration:none;border-radius:16px;">Aktiviraj u aplikaciji →</a>
                  </td>
                </tr>
              </table>
              <p style="margin:14px 0 0;font-size:12px;line-height:18px;color:#94a3b8;">Otvorite ovaj email na telefonu na kojem koristite snovi.fm.</p>
            </td>
          </tr>

          {{-- QR --}}
          <tr>
            <td style="padding:20px 0 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0b1026;border:1px solid #23264a;border-radius:24px;">
                <tr>
                  <td width="160" style="padding:20px;" valign="middle">
                    <img src="{{ $qrUrl }}" width="140" height="140" alt="QR kod za aktivaciju" style="display:block;border:0;border-radius:14px;background:#ffffff;width:140px;height:140px;">
                  </td>
                  <td style="padding:20px 20px 20px 0;" valign="middle">
                    <p style="margin:0;font-size:16px;font-weight:700;color:#ffffff;">Čitate na računaru?</p>
                    <p style="margin:8px 0 0;font-size:14px;line-height:22px;color:#cbd5e1;">Skenirajte QR kod kamerom telefona. Otvorit će se snovi.fm aplikacija sa već upisanim kodom.</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Steps --}}
          <tr>
            <td style="padding:20px 0 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0b1026;border:1px solid #23264a;border-radius:24px;">
                <tr>
                  <td style="padding:28px 28px 8px;">
                    <p style="margin:0;font-size:11px;font-weight:800;letter-spacing:3px;text-transform:uppercase;color:#a78bfa;">Tri koraka do prve priče</p>
                  </td>
                </tr>
                @foreach ([
                  ['1', 'Preuzmite aplikaciju', 'snovi.fm za iPhone ili Android, linkovi su ispod.'],
                  ['2', 'Aktivirajte kod', 'Dodirnite „Aktiviraj u aplikaciji" ili skenirajte QR kod. Kod se upisuje sam, samo potvrdite.'],
                  ['3', 'Ugasite svjetlo', 'Izaberite priču ili ambijent, stavite telefon sa strane i neka dijete sluša zatvorenih očiju.'],
                ] as [$number, $title, $text])
                <tr>
                  <td style="padding:12px 28px;">
                    <table role="presentation" cellpadding="0" cellspacing="0">
                      <tr>
                        <td valign="top" style="padding-right:16px;">
                          <div style="width:34px;height:34px;line-height:34px;border-radius:12px;background:#7c3aed;color:#ffffff;font-weight:800;font-size:14px;text-align:center;">{{ $number }}</div>
                        </td>
                        <td valign="top">
                          <p style="margin:0;font-size:15px;font-weight:700;color:#ffffff;">{{ $title }}</p>
                          <p style="margin:4px 0 0;font-size:14px;line-height:21px;color:#94a3b8;">{{ $text }}</p>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                @endforeach
                <tr><td style="height:16px;"></td></tr>
              </table>
            </td>
          </tr>

          {{-- Stores --}}
          <tr>
            <td align="center" style="padding:24px 0 0;">
              <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="padding:0 6px;">
                    <a href="{{ $appStoreUrl }}" style="display:inline-block;padding:13px 20px;border-radius:14px;background:#ffffff;color:#05060f;font-size:13px;font-weight:800;text-decoration:none;"> App Store</a>
                  </td>
                  <td style="padding:0 6px;">
                    <a href="{{ $googlePlayUrl }}" style="display:inline-block;padding:13px 20px;border-radius:14px;background:#1e1b4b;border:1px solid #3730a3;color:#ffffff;font-size:13px;font-weight:800;text-decoration:none;">▶ Google Play</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td align="center" style="padding:32px 16px 8px;">
              <p style="margin:0;font-family:Georgia,'Times New Roman',serif;font-size:16px;font-style:italic;color:#a78bfa;">Uspavajte maštu. Probudite mir.</p>
              <p style="margin:14px 0 0;font-size:12px;line-height:19px;color:#64748b;">
                Pitanja? Pišite nam na <a href="mailto:podrska@snovi.fm" style="color:#a78bfa;text-decoration:none;">podrska@snovi.fm</a><br>
                Ako dugme ne radi, otvorite: <a href="{{ $link }}" style="color:#a78bfa;">{{ $link }}</a>
              </p>
              <p style="margin:14px 0 0;font-size:11px;color:#475569;">© {{ date('Y') }} snovi.fm · qla.dev</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
