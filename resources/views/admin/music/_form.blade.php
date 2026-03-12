<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">Naziv</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $music->name) }}" required>
    </div>
</div>

<div>
    <label class="form-label">Upload audio</label>
    <input
        type="file"
        name="file_upload"
        class="form-control"
        accept="audio/mpeg,audio/mp3,audio/x-m4a,audio/m4a,audio/mp4,audio/wav,audio/x-wav"
        @required(!$music->exists)
    >
</div>

@if ($music->file)
    <div>
        <label class="form-label d-block">Pregled audio snimka</label>
        <audio controls style="width:100%;">
            <source src="{{ $music->file }}">
        </audio>
    </div>
@endif
