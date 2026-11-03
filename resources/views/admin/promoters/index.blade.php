@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Promoteri</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#promoterModal">Dodaj promotera</button>
</div>

<div class="card p-3">
    <h6 class="text-light mb-3">Influenseri i njihovi kodovi</h6>
    <div class="table-responsive">
        <table class="table align-middle datatable" data-default-order-column="1">
            <thead>
                <tr>
                    <th style="width:72px;">Foto</th>
                    <th>Ime i prezime</th>
                    <th>Stranica</th>
                    <th>PIN</th>
                    <th>Kodovi</th>
                    <th>Status</th>
                    <th class="text-end" style="width:180px;">Akcije</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($promoters as $promoter)
                    <tr>
                        <td>
                            @if ($promoter->photo_url)
                                <img src="{{ $promoter->photo_url }}" alt="" width="48" height="48" style="border-radius:50%; object-fit:cover;">
                            @else
                                <span class="d-inline-flex align-items-center justify-content-center fw-bold" style="width:48px; height:48px; border-radius:50%; background:#8b5cf6; color:#0b1220;">
                                    {{ mb_substr($promoter->first_name, 0, 1) }}{{ mb_substr($promoter->last_name, 0, 1) }}
                                </span>
                            @endif
                        </td>
                        <td class="fw-semibold">
                            {{ $promoter->fullName() }}
                            @if ($promoter->instagram)
                                <div class="small text-muted">{{ '@'.$promoter->instagram }}</div>
                            @endif
                        </td>
                        <td><a href="{{ $promoter->pageLink() }}" target="_blank" rel="noopener">snovi.fm/promoters/{{ $promoter->username }}</a></td>
                        <td class="font-monospace">{{ $promoter->pin ?: '-' }}</td>
                        <td data-order="{{ $promoter->gift_codes_count }}">
                            {{ $promoter->gift_codes_count - $promoter->used_gift_codes_count }} slobodno
                            <span class="text-muted">/ {{ $promoter->used_gift_codes_count }} iskorišteno</span>
                        </td>
                        <td>
                            @if ($promoter->is_active)
                                <span class="badge bg-success">Aktivan</span>
                            @else
                                <span class="badge bg-danger">Skriven</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                <a href="{{ route('admin.promoters.edit', $promoter) }}" class="btn btn-sm btn-primary">Uredi</a>
                                <form action="{{ route('admin.promoters.destroy', $promoter) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Obrisati promotera? Kodovi ostaju među gift kodovima.')">Obriši</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Nema unosa</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="promoterModal" tabindex="-1" aria-labelledby="promoterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="background:#0b1220; color:#e5e7eb;">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="promoterModalLabel">Dodaj promotera</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="promoter-create-form" action="{{ route('admin.promoters.store') }}" method="POST" enctype="multipart/form-data" class="vstack gap-3">
            @csrf
            @include('admin.promoters._form', ['promoter' => new \App\Models\Promoter()])
        </form>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Nazad</button>
        <button type="submit" form="promoter-create-form" class="btn btn-primary">Spasi i dodijeli kodove</button>
      </div>
    </div>
  </div>
</div>
@endsection
