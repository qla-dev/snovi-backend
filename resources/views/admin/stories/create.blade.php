@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        <h4 class="mb-0">Nova priča</h4>
        <div id="upload-status-inline" class="upload-status-inline">
            <div class="upload-pill" data-type="image">
                <span class="upload-dot pending"></span>
                <span>Slika: spremno</span>
            </div>
            <div class="upload-pill" data-type="audio">
                <span class="upload-dot pending"></span>
                <span>Audio: nije odabrano</span>
            </div>
            <div class="upload-pill" data-type="publish">
                <span class="upload-dot pending"></span>
                <span>Draft</span>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="{{ route('admin.stories.index') }}" class="btn btn-outline-light">Nazad</a>
        <button form="story-form" class="btn btn-primary">Snimi</button>
    </div>
</div>

<div class="card p-3">
    <form id="story-form" action="{{ route('admin.stories.store') }}" method="POST" enctype="multipart/form-data" class="vstack gap-3" data-redirect="{{ route('admin.stories.index') }}">
        @csrf
        @include('admin.stories._form')
    </form>
</div>
@endsection
