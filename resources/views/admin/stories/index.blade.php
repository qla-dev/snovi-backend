@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Priče</h4>
    <a href="{{ route('admin.stories.create') }}" class="btn btn-primary">+ Nova priča</a>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naslov</th>
                    <th>Kategorija</th>
                    <th>Trajanje</th>
                    <th>Status</th>
                    <th>Efekti</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stories as $story)
                    <tr>
                        <td class="fw-semibold">{{ $story->slug }}</td>
                        <td>
                            <div>{{ $story->title }}</div>
                            <div class="text-muted small">{{ $story->narrator }}</div>
                        </td>
                        <td>
                            <div>{{ $story->category?->label }}</div>
                            <div class="text-muted small">{{ $story->subcategory?->label }}</div>
                        </td>
                        <td>{{ $story->duration_label ?? '—' }}</td>
                        <td>
                            @if($story->locked)
                                <span class="badge bg-warning text-dark">Zaključano</span>
                            @else
                                <span class="badge bg-success">Otključano</span>
                            @endif
                            @if($story->is_favorite)
                                <span class="badge bg-info text-dark">Favorit</span>
                            @endif
                            @if($story->is_dummy)
                                <span class="badge bg-secondary">Demo</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            @php $levels = collect($story->effects ?? [])->filter(fn($v) => $v > 0); @endphp
                            {{ $levels->count() ? $levels->map(fn($v,$k)=>"$k:$v")->implode(', ') : '—' }}
                        </td>
                        <td>
                            <div class="d-flex gap-2 align-items-center">
                                <a href="{{ route('admin.stories.edit', $story) }}" class="btn btn-sm btn-primary">Uredi</a>
                                <form action="{{ route('admin.stories.destroy', $story) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Obrisati?')">Obriši</button>
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
@endsection
