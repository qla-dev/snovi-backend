@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Muzika</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#musicModal">Dodaj muziku</button>
</div>

<div class="card p-3">
    <h6 class="text-light mb-3">Sva muzika</h6>
    <div class="table-responsive">
        <table class="table align-middle datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naziv</th>
                    <th>Fajl</th>
                    <th>Povezano</th>
                    <th class="text-end" style="width:180px;">Akcije</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($musicItems as $music)
                    <tr>
                        <td class="fw-semibold">#{{ $music->id }}</td>
                        <td>{{ $music->name }}</td>
                        <td class="text-muted small">{{ $music->file ?: '-' }}</td>
                        <td>{{ $music->stories_count }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                <a href="{{ route('admin.music.edit', $music) }}" class="btn btn-sm btn-primary">Uredi</a>
                                <form action="{{ route('admin.music.destroy', $music) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Obrisati?')">Obrisi</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">Nema unosa</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="musicModal" tabindex="-1" aria-labelledby="musicModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="background:#0b1220; color:#e5e7eb;">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="musicModalLabel">Dodaj muziku</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="music-create-form" action="{{ route('admin.music.store') }}" method="POST" enctype="multipart/form-data" class="vstack gap-3">
            @csrf
            @include('admin.music._form', ['music' => new \App\Models\Music()])
        </form>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Nazad</button>
        <button type="submit" form="music-create-form" class="btn btn-primary">Spasi</button>
      </div>
    </div>
  </div>
</div>
@endsection
