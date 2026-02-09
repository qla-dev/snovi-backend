@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Nova priča</h4>
    <div class="d-flex gap-2">
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
