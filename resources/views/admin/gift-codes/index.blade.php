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
                        <td class="fw-semibold">#{{ $giftCode->id }}</td>
                        @php $promoLink = 'https://snovi.fm/promo-code/' . $giftCode->code; @endphp
                        <td class="fw-semibold">{{ $giftCode->code }}</td>
                        <td>{{ $giftCode->email ?: '-' }}</td>
                        <td>
                            <a href="{{ $promoLink }}" target="_blank" rel="noopener">{{ $promoLink }}</a>
                        </td>
                        <td>
                            <img
                                src="https://api.qrserver.com/v1/create-qr-code/?size=96x96&data={{ urlencode($promoLink) }}"
                                alt="QR {{ $giftCode->code }}"
                                width="72"
                                height="72"
                                style="border-radius:8px; background:#fff; padding:4px;"
                            >
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
                Kod ce biti automatski generisan: 12 znakova, tacno 6 slova i 6 cifara.
            </div>
            <div>
                <label class="form-label" for="gift-code-expires-at">Vazi do</label>
                <input
                    id="gift-code-expires-at"
                    type="date"
                    name="expires_at"
                    class="form-control"
                    value="{{ old('expires_at') }}"
                >
                <div class="text-muted small mt-1">Ako ostane prazno, kod vrijedi godinu dana od danas.</div>
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
@endsection
