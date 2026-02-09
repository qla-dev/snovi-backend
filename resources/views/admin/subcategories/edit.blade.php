@extends('layouts.admin')

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Uredi potkategoriju</h4>
    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline-light">Nazad</a>
</div>

<div class="card p-3">
    <form action="{{ route('admin.subcategories.update', $subcategory) }}" method="POST" class="vstack gap-3">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Kategorija</label>
                <select name="category_id" class="form-select" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $subcategory->category_id) == $category->id)>
                            {{ $category->label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $subcategory->slug) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Naziv</label>
                <input type="text" name="label" class="form-control" value="{{ old('label', $subcategory->label) }}" required>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <div class="form-check mt-1">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $subcategory->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Aktivna</label>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary">Spasi promjene</button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.subcategories.index') }}">Otkaži</a>
        </div>
    </form>
</div>
@endsection
