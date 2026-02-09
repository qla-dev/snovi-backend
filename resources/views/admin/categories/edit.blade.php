@extends('layouts.admin')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Uredi kategoriju</h4>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-light">Nazad</a>
</div>

<div class="card p-3">
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="vstack gap-3">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Naziv</label>
                <input type="text" name="label" class="form-control" value="{{ old('label', $category->label) }}" required>
            </div>
        </div>
        <div>
            <label class="form-label">Opis</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
        </div>
        <div class="d-flex align-items-center">
            <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Aktivna</label>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary">Spasi promjene</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.categories.index') }}">Otkaži</a>
        </div>
    </form>
</div>
@endsection
