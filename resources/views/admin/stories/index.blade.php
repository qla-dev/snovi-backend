@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Price</h4>
    <a href="{{ route('admin.stories.create') }}" class="btn btn-primary">+ Nova prica</a>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle datatable" data-default-order-column="5" data-default-order-dir="desc">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naslov</th>
                    <th>Kategorija</th>
                    <th>Trajanje</th>
                    <th>Muzika</th>
                    <th>Datum</th>
                    <th>Objava</th>
                    <th>Status</th>
                    <th>Efekti</th>
                    <th class="text-end" style="width:180px;">Akcije</th>
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
                        <td>{{ $story->duration_label ?? '-' }}</td>
                        <td>
                            @if($story->music)
                                <div>{{ $story->music->name }}</div>
                                <div class="text-muted small">#{{ $story->music->id }}</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td data-order="{{ optional($story->created_at)->timestamp ?? 0 }}">
                            {{ optional($story->created_at)->format('d.m.Y') ?? '-' }}
                        </td>
                        <td>
                            @if($story->publish_state === 'objavljeno')
                                <span class="badge bg-success">Objavljeno</span>
                            @else
                                <span class="badge bg-warning text-dark">Draft</span>
                            @endif
                        </td>
                        <td>
                            @if($story->locked)
                                <span class="badge bg-warning text-dark">Zakljucano</span>
                            @else
                                <span class="badge bg-success">Otkljucano</span>
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
                            {{ $levels->count() ? $levels->map(fn($v, $k) => "$k:$v")->implode(', ') : '-' }}
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                <a href="{{ route('admin.stories.edit', $story) }}" class="btn btn-sm btn-primary">Uredi</a>
                                <form action="{{ route('admin.stories.destroy', $story) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Obrisati?')">Obriđi</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted">Nema unosa</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
