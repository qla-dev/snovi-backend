@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height:60vh;">
    <div class="card p-4" style="min-width:360px; max-width:420px;">
        <h4 class="mb-3 text-center text-white">Prijava</h4>
        <form action="{{ route('login.post') }}" method="POST" class="vstack gap-3">
            @csrf
            <div>
                <label class="form-label">Korisničko ime</label>
                <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
            </div>
            <div>
                <label class="form-label">Lozinka</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger py-2 mb-0">
                    {{ $errors->first() }}
                </div>
            @endif
            <button class="btn btn-primary w-100 mt-2">Prijavi se</button>
        </form>
    </div>
</div>
@endsection
