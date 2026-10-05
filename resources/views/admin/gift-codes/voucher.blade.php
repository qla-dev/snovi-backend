@php
    $link = $giftCode->promoLink();
    $codeGroups = implode(' ', str_split($giftCode->code, 4));
    $qr = 'https://api.qrserver.com/v1/create-qr-code/?format=png&size=600x600&margin=16&color=1e1b4b&data=' . urlencode($link);
    $expires = optional($giftCode->expires_at)->setTimezone('Europe/Sarajevo')?->format('d.m.Y');
@endphp
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>snovi.fm vaučer {{ $giftCode->code }}</title>
    <style>
        @page { size: A4 portrait; margin: 0; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        html, body { margin: 0; background: #1a1a22; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #e2e8f0; }

        .toolbar { position: sticky; top: 0; z-index: 5; display: flex; gap: 10px; justify-content: center; padding: 14px; background: #0b0f1c; border-bottom: 1px solid #23264a; }
        .toolbar a, .toolbar button { font: 700 13px/1 inherit; font-family: inherit; letter-spacing: .06em; text-transform: uppercase; text-decoration: none; padding: 12px 18px; border-radius: 12px; border: 1px solid #3730a3; color: #fff; background: transparent; cursor: pointer; }
        .toolbar .primary { background: #7c3aed; border-color: #7c3aed; }

        .page { position: relative; width: 210mm; height: 297mm; margin: 24px auto; overflow: hidden; background: radial-gradient(ellipse at 50% 0%, rgba(124,58,237,.38), transparent 60%), #050505; padding: 13mm 18mm 12mm; display: flex; flex-direction: column; }
        .star { position: absolute; border-radius: 50%; background: #fff; }
        .moon { position: absolute; right: 22mm; top: 34mm; width: 34mm; height: 34mm; border-radius: 50%; box-shadow: 7mm 4mm 0 0 #fde68a; filter: drop-shadow(0 0 6mm rgba(253,230,138,.45)); }

        header { position: relative; display: flex; align-items: center; justify-content: space-between; }
        header img { height: 20mm; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 999px; border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.05); font-size: 10px; font-weight: 900; letter-spacing: .2em; text-transform: uppercase; color: #ddd6fe; }

        .hero { position: relative; text-align: center; margin-top: 6mm; }
        .eyebrow { font-size: 11px; font-weight: 900; letter-spacing: .3em; text-transform: uppercase; color: #c4b5fd; }
        h1 { margin: 6mm 0 0; font-family: Georgia, 'Times New Roman', serif; font-size: 38px; line-height: 1; color: #fff; }
        .lead { margin: 5mm auto 0; max-width: 125mm; font-size: 15px; line-height: 1.6; color: #cbd5e1; }

        /* The glow is a gradient layer, not a blurred box-shadow: mobile browsers print a big blurred shadow as a solid rectangle. */
        .ticket-wrap { position: relative; margin: 8mm auto 0; width: 150mm; }
        .ticket-glow { position: absolute; left: -22mm; right: -22mm; top: -8mm; bottom: -26mm; background: radial-gradient(ellipse 50% 50% at 50% 58%, rgba(139,92,246,.85), rgba(124,58,237,.38) 52%, rgba(124,58,237,0) 100%); }
        .ticket { position: relative; background: #fff; color: #1e1b4b; border-radius: 9mm; padding: 7mm 10mm; text-align: center; }
        .ticket::before, .ticket::after { content: ''; position: absolute; top: 50%; width: 9mm; height: 9mm; margin-top: -4.5mm; border-radius: 50%; background: #050505; }
        .ticket::before { left: -4.5mm; } .ticket::after { right: -4.5mm; }
        .ticket .label { font-size: 11px; font-weight: 900; letter-spacing: .3em; text-transform: uppercase; color: #7c3aed; }
        .ticket .code { margin-top: 4mm; font-family: 'SFMono-Regular', Menlo, Consolas, monospace; font-size: 34px; font-weight: 900; letter-spacing: .18em; }
        .ticket .plan { margin-top: 2mm; font-size: 13px; color: #64748b; }
        .ticket hr { border: 0; border-top: 2px dashed #e2e8f0; margin: 5mm 0; }
        .ticket .row { display: flex; align-items: center; gap: 7mm; text-align: left; }
        .ticket .row img { width: 33mm; height: 33mm; border-radius: 4mm; }
        .ticket .row b { display: block; font-size: 16px; color: #1e1b4b; }
        .ticket .row p { margin: 2mm 0 0; font-size: 13px; line-height: 1.55; color: #475569; }
        .ticket .row .link { margin-top: 3mm; font-size: 11px; font-weight: 700; color: #7c3aed; word-break: break-all; }

        .steps { position: relative; margin: 7mm auto 0; width: 150mm; border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.03); border-radius: 7mm; padding: 5mm 8mm 6mm; }
        .steps h2 { margin: 0 0 5mm; font-size: 11px; font-weight: 900; letter-spacing: .3em; text-transform: uppercase; color: #c4b5fd; }
        .step { display: flex; gap: 5mm; margin-top: 3mm; }
        .step span { flex: 0 0 9mm; height: 9mm; border-radius: 3mm; background: #7c3aed; color: #fff; font-weight: 900; font-size: 13px; display: flex; align-items: center; justify-content: center; }
        .step b { display: block; color: #fff; font-size: 14px; }
        .step p { margin: 1mm 0 0; font-size: 12.5px; line-height: 1.5; color: #94a3b8; }

        footer { position: relative; margin-top: auto; padding-top: 6mm; text-align: center; }
        footer .stores { display: flex; justify-content: center; gap: 4mm; }
        footer .stores span { padding: 3mm 6mm; border-radius: 3mm; font-size: 12px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }
        footer .stores .ios { background: #fff; color: #05060f; }
        footer .stores .android { border: 1px solid #3730a3; background: #1e1b4b; color: #fff; }
        footer .motto { margin-top: 5mm; font-family: Georgia, serif; font-style: italic; color: #a78bfa; font-size: 15px; }
        footer .meta { margin-top: 2mm; font-size: 11px; color: #64748b; }

        @media print {
            html, body { background: #050505; }
            .toolbar { display: none; }
            .page { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('admin.gift-codes.index') }}">← Nazad</a>
        <a href="{{ $link }}" target="_blank" rel="noopener">Otvori stranicu</a>
        <button type="button" class="primary" onclick="window.print()">Preuzmi PDF</button>
    </div>

    <div class="page">
        @for ($i = 0; $i < 46; $i++)
            @php $size = $i % 5 === 0 ? 3 : 2; @endphp
            <span class="star" style="left: {{ ($i * 37) % 100 }}%; top: {{ ($i * 53) % 100 }}%; width: {{ $size }}px; height: {{ $size }}px; opacity: {{ 0.25 + ($i % 4) * 0.18 }};"></span>
        @endfor
        <div class="moon"></div>

        <header>
            <img src="https://snovi.fm/logo.png" alt="snovi.fm">
            <span class="pill">🎁 Vaučer</span>
        </header>

        <div class="hero">
            <div class="eyebrow">✨ snovi.fm premium</div>
            <h1>Vaš poklon za<br>mirne večeri</h1>
            <p class="lead">Preuzmite aplikaciju snovi.fm i aktivirajte kod. Priče, uspavanke i ambijenti otključavaju se odmah.</p>
        </div>

        <div class="ticket-wrap">
        <div class="ticket-glow"></div>
        <div class="ticket">
            <div class="label">Kod za aktivaciju</div>
            <div class="code">{{ $codeGroups }}</div>
            <div class="plan">{{ $giftCode->planLabel() }}{{ $expires ? ' · vrijedi do ' . $expires : '' }}</div>
            <hr>
            <div class="row">
                <img src="{{ $qr }}" alt="QR kod">
                <div>
                    <b>Skenirajte kamerom telefona</b>
                    <p>Otvara se snovi.fm aplikacija sa već upisanim kodom. Bez aplikacije, link vodi do preuzimanja.</p>
                    <div class="link">{{ $link }}</div>
                </div>
            </div>
        </div>
        </div>

        <div class="steps">
            <h2>☾ Tri koraka</h2>
            @foreach ([
                ['Preuzmite snovi.fm', 'Besplatno na App Storeu i Google Playu.'],
                ['Skenirajte QR kod', 'Ili otvorite link na telefonu. Kod se upisuje sam, samo potvrdite aktivaciju.'],
                ['Laku noć', 'Izaberite priču, ugasite svjetlo i neka dijete sluša zatvorenih očiju.'],
            ] as $index => [$title, $text])
                <div class="step">
                    <span>{{ $index + 1 }}</span>
                    <div><b>{{ $title }}</b><p>{{ $text }}</p></div>
                </div>
            @endforeach
        </div>

        <footer>
            <div class="stores"><span class="ios"> App Store</span><span class="android">▶ Google Play</span></div>
            <div class="motto">Uspavajte maštu. Probudite mir.</div>
            <div class="meta">snovi.fm · podrska@snovi.fm</div>
        </footer>
    </div>

    @if (request()->boolean('print'))
        <script>
            window.addEventListener('load', () => setTimeout(() => window.print(), 400));
        </script>
    @endif
</body>
</html>
