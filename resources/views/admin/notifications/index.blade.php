@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Notifikacije</h4>
        <div class="text-muted small">Aktivnih tokena: {{ $activeTokenCount }}</div>
    </div>
</div>

<div class="card p-3 mb-4">
    <h6 class="text-light mb-3">Posalji notifikaciju</h6>
    <form action="{{ route('admin.notifications.store') }}" method="POST" class="vstack gap-3">
        @csrf
        <div>
            <label class="form-label" for="notification-body">Body</label>
            <input
                id="notification-body"
                type="text"
                name="body"
                class="form-control"
                value="{{ old('body', $defaultBody) }}"
                required
                maxlength="190"
            >
        </div>
        <div class="form-check form-switch">
            <input
                class="form-check-input"
                type="checkbox"
                role="switch"
                id="notification-open-external"
                name="open_external"
                value="1"
                @checked(old('open_external'))
            >
            <label class="form-check-label" for="notification-open-external">
                Otvori vanjski link umjesto aplikacije
            </label>
        </div>
        <div>
            <label class="form-label" for="notification-link-url">Link</label>
            <input
                id="notification-link-url"
                type="url"
                name="link_url"
                class="form-control"
                value="{{ old('link_url') }}"
                placeholder="https://apps.apple.com/... ili https://play.google.com/..."
                maxlength="2000"
            >
            <div class="form-text">Ako je prekidač isključen, dodir na notifikaciju samo otvara aplikaciju.</div>
        </div>
        <div>
            <label class="form-label" for="notification-description">Desc</label>
            <textarea
                id="notification-description"
                name="description"
                class="form-control"
                rows="3"
                required
                maxlength="500"
            >{{ old('description', $defaultDescription) }}</textarea>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary" onclick="return confirm('Poslati notifikaciju svim aktivnim uredjajima?')">
                Posalji
            </button>
        </div>
    </form>
</div>

<div class="card p-3">
    <h6 class="text-light mb-3">Historija</h6>
    <div class="table-responsive">
        <table class="table align-middle datatable" data-default-order-column="0" data-default-order-dir="desc">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Body</th>
                    <th>Desc</th>
                    <th>Akcija</th>
                    <th>Uredjaji</th>
                    <th>Uspjesno</th>
                    <th>Greske</th>
                    <th>Poslano</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td class="fw-semibold">#{{ $notification->id }}</td>
                        <td>{{ $notification->body }}</td>
                        <td>{{ $notification->description }}</td>
                        <td>
                            @if(($notification->data['action'] ?? 'open_app') === 'external_url')
                                <a href="{{ $notification->data['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer">Vanjski link</a>
                            @else
                                Otvori aplikaciju
                            @endif
                        </td>
                        <td>{{ $notification->recipient_count }}</td>
                        <td><span class="badge bg-success">{{ $notification->success_count }}</span></td>
                        <td><span class="badge bg-danger">{{ $notification->failure_count }}</span></td>
                        <td>{{ optional($notification->sent_at)->format('d.m.Y H:i') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Nema poslanih notifikacija</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
