@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">{{ $promoter->fullName() }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.promoters.index') }}" class="btn btn-outline-light">Nazad</a>
        <a href="{{ $promoter->pageLink() }}" target="_blank" rel="noopener" class="btn btn-outline-light">Otvori stranicu</a>
        <button form="promoter-form" class="btn btn-primary">Spasi</button>
    </div>
</div>

<form id="promoter-form" action="{{ route('admin.promoters.update', $promoter) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card p-3 mb-4">
        <h6 class="text-light mb-3">Profil</h6>
        <div class="vstack gap-3">
            @include('admin.promoters._form')
        </div>
    </div>

    <div class="card p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h6 class="text-light mb-1">Kodovi promotera</h6>
                <div class="small text-muted">Označite slobodne gift kodove koje promoter dijeli. Iskorišteni kodovi ostaju vezani za njega.</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label for="new_codes" class="form-label mb-0 small text-nowrap">Generiši nove</label>
                <input type="number" name="new_codes" id="new_codes" class="form-control form-control-sm" style="width:5rem;" min="0" max="50" value="0">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width:48px;"><input class="form-check-input" type="checkbox" id="toggle-all-codes" aria-label="Označi sve"></th>
                        <th>Kod</th>
                        <th>Vazi do</th>
                        <th>Status</th>
                        <th>Link</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($giftCodes as $giftCode)
                        @php $locked = $giftCode->used || $giftCode->isExpired(); @endphp
                        <tr>
                            <td>
                                @if ($locked)
                                    <input type="hidden" name="gift_code_ids[]" value="{{ $giftCode->id }}">
                                    <input class="form-check-input" type="checkbox" checked disabled aria-label="{{ $giftCode->code }}">
                                @else
                                    <input class="form-check-input code-checkbox" type="checkbox" name="gift_code_ids[]" value="{{ $giftCode->id }}" id="code-{{ $giftCode->id }}" {{ $giftCode->promoter_id === $promoter->id ? 'checked' : '' }}>
                                @endif
                            </td>
                            <td class="fw-semibold"><label for="code-{{ $giftCode->id }}" class="mb-0">{{ $giftCode->code }}</label></td>
                            <td>{{ optional($giftCode->expires_at)->format('d.m.Y') ?? '-' }}</td>
                            <td>
                                @if ($giftCode->used)
                                    <span class="badge bg-danger">Iskoristen {{ optional($giftCode->used_date)->format('d.m.Y') }}</span>
                                @elseif ($giftCode->isExpired())
                                    <span class="badge badge-muted">Istekao</span>
                                @elseif ($giftCode->promoter_id === $promoter->id)
                                    <span class="badge bg-success">Dodijeljen</span>
                                @else
                                    <span class="badge badge-muted">Slobodan</span>
                                @endif
                            </td>
                            <td><a href="{{ $giftCode->promoLink() }}" target="_blank" rel="noopener" class="small">{{ $giftCode->promoLink() }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Nema slobodnih kodova. Upišite broj u „Generiši nove" i spasite.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('toggle-all-codes');
        const boxes = document.querySelectorAll('.code-checkbox');
        if (!toggle) return;
        toggle.addEventListener('change', () => boxes.forEach((box) => { box.checked = toggle.checked; }));
    });
</script>
@endpush
