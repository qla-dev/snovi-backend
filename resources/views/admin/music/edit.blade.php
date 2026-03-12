@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Uredi muziku</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.music.index') }}" class="btn btn-outline-light">Nazad</a>
        <button form="music-form" class="btn btn-primary">Spasi</button>
    </div>
</div>

<div class="card p-3">
    <form id="music-form" action="{{ route('admin.music.update', $music) }}" method="POST" enctype="multipart/form-data" class="vstack gap-3">
        @csrf
        @method('PUT')
        @include('admin.music._form')
        <div class="text-muted small">ID: #{{ $music->id }} | Povezane price: {{ $music->stories_count }}</div>
    </form>
</div>
@endsection
