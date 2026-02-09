@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Potkategorije</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subcategoryModal">Dodaj potkategoriju</button>
</div>

<div class="card p-3">
    <h6 class="text-light mb-3">Sve potkategorije</h6>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Slug</th>
                    <th>Naziv</th>
                    <th>Kategorija</th>
                    <th>Status</th>
                    <th class="text-end" style="width:180px;">Akcije</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subcategories as $sub)
                    <tr>
                        <td class="fw-semibold">{{ $sub->slug }}</td>
                        <td>{{ $sub->label }}</td>
                        <td class="text-muted">{{ $sub->category?->label ?? '-' }}</td>
                        <td>
                            @if($sub->is_active)
                                <span class="badge bg-success">Aktivna</span>
                            @else
                                <span class="badge bg-secondary">Sakrivena</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                <a href="{{ route('admin.subcategories.edit', $sub) }}" class="btn btn-sm btn-primary">Uredi</a>
                                <form action="{{ route('admin.subcategories.destroy', $sub) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Obrisati?')">Obriši</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Nema unosa</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Dodaj potkategoriju -->
<div class="modal fade" id="subcategoryModal" tabindex="-1" aria-labelledby="subcategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="background:#0b1220; color:#e5e7eb;">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="subcategoryModalLabel">Dodaj potkategoriju</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="subcategory-create-form" action="{{ route('admin.subcategories.store') }}" method="POST" class="vstack gap-3">
            @csrf
            <div>
                <label class="form-label">Kategorija</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Odaberi...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" placeholder="popularno" required>
            </div>
            <div>
                <label class="form-label">Naziv</label>
                <input type="text" name="label" class="form-control" placeholder="🔥 Popularno" required>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" name="is_active" id="sub-active-modal" checked>
                <label class="form-check-label" for="sub-active-modal">Aktivna</label>
            </div>
        </form>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Nazad</button>
        <button type="submit" form="subcategory-create-form" class="btn btn-primary">Spasi</button>
      </div>
    </div>
  </div>
</div>
@endsection
