<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Ime</label>
        <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $promoter->first_name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Prezime</label>
        <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $promoter->last_name) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Username</label>
        <div class="input-group">
            <span class="input-group-text" style="background:#1f2937; color:#9ca3af; border-color:#1f2937;">snovi.fm/promoters/</span>
            <input type="text" name="username" class="form-control" value="{{ old('username', $promoter->username) }}" placeholder="ime.prezime" required>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Instagram</label>
        <div class="input-group">
            <span class="input-group-text" style="background:#1f2937; color:#9ca3af; border-color:#1f2937;">@</span>
            <input type="text" name="instagram" class="form-control" value="{{ old('instagram', $promoter->instagram) }}" placeholder="Opcionalno">
        </div>
    </div>
</div>
<div>
    <label class="form-label">Kratki opis</label>
    <input type="text" name="headline" class="form-control" value="{{ old('headline', $promoter->headline) }}" maxlength="160" placeholder="npr. Mama dvoje djece i ljubiteljica bajki">
</div>
<div>
    <label class="form-label">Poruka pratiocima</label>
    <textarea name="bio" class="form-control" rows="3" maxlength="2000" placeholder="Opcionalno: prikazuje se na stranici promotera">{{ old('bio', $promoter->bio) }}</textarea>
</div>
<div>
    <label class="form-label">Fotografija</label>
    <input type="file" name="photo_upload" class="form-control" accept="image/jpeg,image/png,image/webp">
    <div class="form-text text-muted">Kvadratna slika izgleda najbolje. Koristi se i kao slika pri dijeljenju linka.</div>
</div>
@if ($promoter->photo_url)
    <div class="d-flex align-items-center gap-3">
        <img src="{{ $promoter->photo_url }}" alt="{{ $promoter->fullName() }}" width="72" height="72" style="border-radius:50%; object-fit:cover;">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" name="remove_photo" id="remove_photo">
            <label class="form-check-label" for="remove_photo">Ukloni fotografiju</label>
        </div>
    </div>
@endif
<div class="form-check">
    <input class="form-check-input" type="checkbox" value="1" name="is_active" id="promoter-active-{{ $promoter->id ?? 'new' }}" {{ old('is_active', $promoter->exists ? $promoter->is_active : true) ? 'checked' : '' }}>
    <label class="form-check-label" for="promoter-active-{{ $promoter->id ?? 'new' }}">Aktivan (stranica je javna)</label>
</div>
