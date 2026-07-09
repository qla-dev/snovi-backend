@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Gift kodovi</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#giftCodeModal">Dodaj kod</button>
</div>

<div class="card p-3">
    <h6 class="text-light mb-3">Svi gift kodovi</h6>
    <div class="table-responsive">
        <table class="table align-middle datatable" data-default-order-column="0" data-default-order-dir="desc">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kod / broj licence</th>
                    <th>Email</th>
                    <th>Deep link</th>
                    <th>QR</th>
                    <th>Vazi do</th>
                    <th>Status</th>
                    <th>Datum koristenja</th>
                    <th class="text-end" style="width:220px;">Akcije</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($giftCodes as $giftCode)
                    <tr>
                        <td class="fw-semibold" data-order="{{ $giftCode->id }}">#{{ $giftCode->id }}</td>
                        @php $promoLink = 'https://snovi.fm/promo-code/' . $giftCode->code; @endphp
                        <td class="fw-semibold">{{ $giftCode->code }}</td>
                        <td>{{ $giftCode->email ?: '-' }}</td>
                        <td>
                            <a href="{{ $promoLink }}" target="_blank" rel="noopener">{{ $promoLink }}</a>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="btn p-0 border-0 bg-transparent"
                                data-bs-toggle="modal"
                                data-bs-target="#giftQrModal"
                                data-qr-src="https://api.qrserver.com/v1/create-qr-code/?size=768x768&data={{ urlencode($promoLink) }}"
                                data-qr-code="{{ $giftCode->code }}"
                                data-qr-link="{{ $promoLink }}"
                                data-qr-download="{{ route('admin.gift-codes.qr', $giftCode) }}"
                                aria-label="Uvecaj QR {{ $giftCode->code }}"
                            >
                                <img
                                    src="https://api.qrserver.com/v1/create-qr-code/?size=96x96&data={{ urlencode($promoLink) }}"
                                    alt="QR {{ $giftCode->code }}"
                                    width="72"
                                    height="72"
                                    style="border-radius:8px; background:#fff; padding:4px; cursor: zoom-in;"
                                >
                            </button>
                        </td>
                        <td>{{ optional($giftCode->expires_at)->format('d.m.Y') ?? '-' }}</td>
                        <td>
                            @if($giftCode->used)
                                <span class="badge bg-danger">Iskoristen</span>
                            @else
                                <span class="badge bg-success">Aktivan</span>
                            @endif
                        </td>
                        <td>{{ optional($giftCode->used_date)->format('d.m.Y H:i') ?? '-' }}</td>
                        <td class="text-end">
                            <a
                                href="{{ route('admin.gift-codes.qr', $giftCode) }}"
                                class="btn btn-sm btn-outline-light"
                            >
                                Preuzmi
                            </a>
                            @if(!$giftCode->used)
                                <form action="{{ route('admin.gift-codes.expire', $giftCode) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Oznaciti kod kao iskoristen?')">
                                        Expire
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Nema unosa</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="giftCodeModal" tabindex="-1" aria-labelledby="giftCodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:#0b1220; color:#e5e7eb;">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="giftCodeModalLabel">Dodaj gift kod</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="gift-code-create-form" action="{{ route('admin.gift-codes.store') }}" method="POST" class="vstack gap-3">
            @csrf
            <div class="alert alert-info mb-0">
                Kod ce biti automatski generisan: 12 znakova, tacno 6 slova i 6 cifara. Vrijedi godinu dana od kreiranja.
            </div>
        </form>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Nazad</button>
        <button type="submit" form="gift-code-create-form" class="btn btn-primary">Spasi</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="giftQrModal" tabindex="-1" aria-labelledby="giftQrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:#0b1220; color:#e5e7eb;">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="giftQrModalLabel">QR kod</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img
            id="giftQrModalImage"
            src=""
            alt="QR kod"
            width="320"
            height="320"
            class="img-fluid"
            style="border-radius:16px; background:#fff; padding:12px;"
        >
        <div id="giftQrModalCode" class="fw-semibold mt-3"></div>
        <a id="giftQrModalLink" href="#" target="_blank" rel="noopener" class="small d-block mt-1"></a>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Nazad</button>
        <a id="giftQrModalDownload" href="#" class="btn btn-primary">Preuzmi SVG</a>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('giftQrModal');
        const image = document.getElementById('giftQrModalImage');
        const code = document.getElementById('giftQrModalCode');
        const link = document.getElementById('giftQrModalLink');
        const download = document.getElementById('giftQrModalDownload');

        if (!modal || !image || !code || !link || !download) return;

        modal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const qrSrc = trigger.getAttribute('data-qr-src') || '';
            const qrCode = trigger.getAttribute('data-qr-code') || '';
            const qrLink = trigger.getAttribute('data-qr-link') || '#';
            const qrDownload = trigger.getAttribute('data-qr-download') || '#';

            image.src = qrSrc;
            image.alt = `QR ${qrCode}`;
            code.textContent = qrCode;
            link.href = qrLink;
            link.textContent = qrLink;
            download.href = qrDownload;
        });

        modal.addEventListener('hidden.bs.modal', () => {
            image.src = '';
        });
    });
</script>
@endpush
