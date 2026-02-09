@php
    $effectLabels = [
        'ocean' => 'Okean',
        'rain' => 'Kiša',
        'fire' => 'Vatra',
        'leaves' => 'Lišće',
        'river' => 'Rijeka',
        'birds' => 'Ptice',
        'fan' => 'Ventilator',
        'snow' => 'Snijeg',
        'train' => 'Voz',
        'crickets' => 'Cvrči',
    ];
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Naslov</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $story->title) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Slug (ID)</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $story->slug) }}" placeholder="breathing-woods">
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Narator</label>
        <input type="text" name="narrator" class="form-control" value="{{ old('narrator', $story->narrator) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Trajanje (label)</label>
        <input type="text" name="duration_label" class="form-control" value="{{ old('duration_label', $story->duration_label) }}" placeholder="32 min">
    </div>
    <div class="col-md-4">
        <label class="form-label">Objavljeno</label>
        <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($story->published_at)->format('Y-m-d\TH:i')) }}">
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Kategorija</label>
        <select name="category_id" id="category-select" class="form-select" required>
            <option value="">Odaberi...</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $story->category_id) == $category->id)>{{ $category->label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Potkategorija</label>
        <select name="subcategory_id" id="subcategory-select" class="form-select">
            <option value="">--</option>
            @foreach($subcategories as $sub)
                <option value="{{ $sub->id }}" data-category="{{ $sub->category_id }}" @selected(old('subcategory_id', $story->subcategory_id) == $sub->id)>
                    {{ $sub->label }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <input type="hidden" name="image_url" value="{{ old('image_url', $story->image_url) }}">
        <label class="form-label">Upload sliku <small class="text-muted">(1536x1024, JPG/PNG)</small></label>
        <input type="file" name="image_upload" class="form-control" accept="image/png,image/jpeg">
        <div class="mt-2">
            <small class="text-muted">Pregled slike</small>
            <div class="rounded p-0" style="min-height:120px;">
                <img id="story-image-preview" src="{{ old('image_url', $story->image_url) }}" alt="Preview" style="max-width:100%; max-height:200px; display: {{ old('image_url', $story->image_url) ? 'block' : 'none' }};">
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <input type="hidden" name="audio_url" value="{{ old('audio_url', $story->audio_url) }}">
        <label class="form-label">Upload audio (MP3/M4A)</label>
        <input type="file" name="audio_upload" class="form-control" accept="audio/mpeg,audio/mp3,audio/x-m4a,audio/m4a">
        <div class="mt-2">
            <small class="text-muted">Pregled audio snimka</small>
            <audio id="story-audio-preview" controls style="width:100%; display: {{ old('audio_url', $story->audio_url) || $story->audio_path ? 'block' : 'none' }};">
                @if(old('audio_url', $story->audio_url))
                    <source src="{{ old('audio_url', $story->audio_url) }}">
                @elseif($story->audio_url)
                    <source src="{{ $story->audio_url }}">
                @endif
                @if($story->audio_path)
                    <source src="{{ Storage::url($story->audio_path) }}">
                @endif
            </audio>
        </div>
    </div>
</div>

<div>
    <label class="form-label">Opis</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $story->description) }}</textarea>
</div>

<div class="mt-3">
    <label class="form-label d-block mb-2">Efekti (0 - 10)</label>
    <div class="card p-3" style="background:#0b1220; border:1px solid #1f2937;">
        <div class="row g-3">
            @foreach($effectLabels as $key => $label)
                <div class="col-md-4">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>{{ $label }}</span>
                        <span class="text-muted small" id="level-{{ $key }}">{{ old("effects.$key", $story->effects[$key] ?? 0) }}</span>
                    </label>
                    <input type="range" class="form-range" min="0" max="10" step="1"
                        name="effects[{{ $key }}]"
                        value="{{ old("effects.$key", $story->effects[$key] ?? 0) }}"
                        oninput="document.getElementById('level-{{ $key }}').innerText = this.value;"
                        style="accent-color:#8b5cf6;">
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="toggle-row row g-3 mt-2 align-items-center text-center">
    <div class="col-md-4 d-flex flex-column align-items-center justify-content-center gap-1">
        <input class="form-check-input" type="checkbox" name="is_dummy" value="1" id="is_dummy" {{ old('is_dummy', $story->is_dummy) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_dummy">Demo sadržaj</label>
    </div>
    <div class="col-md-4 d-flex flex-column align-items-center justify-content-center gap-1">
        <input class="form-check-input" type="checkbox" name="locked" value="1" id="locked" {{ old('locked', $story->locked) ? 'checked' : '' }}>
        <label class="form-check-label" for="locked">Zaključano</label>
    </div>
    <div class="col-md-4 d-flex flex-column align-items-center justify-content-center gap-1">
        <input class="form-check-input" type="checkbox" name="is_favorite" value="1" id="is_favorite" {{ old('is_favorite', $story->is_favorite) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_favorite">Favorit</label>
    </div>
</div>

<div id="upload-status" class="upload-status-bar">
    <div class="upload-pill" data-type="image">
        <span class="upload-dot pending"></span>
        <span>Slika: spremno</span>
    </div>
    <div class="upload-pill" data-type="audio">
        <span class="upload-dot pending"></span>
        <span>Audio: nije odabrano</span>
    </div>
</div>

@push('scripts')
<script>
(() => {
  const imgUrlInput = document.querySelector('input[name="image_url"]');
  const imgUploadInput = document.querySelector('input[name="image_upload"]');
  const imgPreview = document.getElementById('story-image-preview');

  const audioUrlInput = document.querySelector('input[name="audio_url"]');
  const audioUploadInput = document.querySelector('input[name="audio_upload"]');
  const audioPreview = document.getElementById('story-audio-preview');
  const categorySelect = document.getElementById('category-select');
  const subcategorySelect = document.getElementById('subcategory-select');
  const statusBar = document.getElementById('upload-status-inline') || document.getElementById('upload-status');
  const imgPill = statusBar?.querySelector('[data-type="image"]');
  const audioPill = statusBar?.querySelector('[data-type="audio"]');
  const publishPill = statusBar?.querySelector('[data-type="publish"]');
  const form = imgUrlInput?.closest('form');
  let progressTimer = null;
  let manualProgressTimer = null;

  const hasImageSelected = () => !!(imgUrlInput?.value?.trim() || imgUploadInput?.files?.length);
  const hasAudioSelected = () => !!(audioUrlInput?.value?.trim() || audioUploadInput?.files?.length);

  function updateStatusVisibility() {
    if (!statusBar) return;
    const hasImage = hasImageSelected();
    const hasAudio = hasAudioSelected();
    statusBar.classList.toggle('show', true);
    setPillState(imgPill, hasImage ? 'ready' : 'pending', hasImage ? 'Slika: spremno' : 'Slika: nije odabrana');
    setPillState(audioPill, hasAudio ? 'ready' : 'pending', hasAudio ? 'Audio: spremno' : 'Audio: nije odabrano');
    if (publishPill) {
      const allReady = hasImage && hasAudio;
      setPillState(publishPill, allReady ? 'ready' : 'pending', allReady ? 'Objavljeno' : 'Draft');
    }
  }

  function setPillState(pill, state, label) {
    if (!pill) return;
    const dot = pill.querySelector('.upload-dot');
    pill.querySelector('span:nth-child(2)').textContent = label;
    dot.classList.remove('pending', 'uploading', 'error', 'ready');
    dot.classList.add(state);
  }

  function showImage(src) {
    if (!imgPreview) return;
    if (src) {
      imgPreview.src = src;
      imgPreview.style.display = 'block';
    } else {
      imgPreview.removeAttribute('src');
      imgPreview.style.display = 'none';
    }
    updateStatusVisibility();
  }

  function showAudio(src) {
    if (!audioPreview) return;
    if (src) {
      audioPreview.src = src;
      audioPreview.style.display = 'block';
      audioPreview.load();
    } else {
      audioPreview.removeAttribute('src');
      audioPreview.style.display = 'none';
    }
    updateStatusVisibility();
  }

  if (imgUrlInput) {
    imgUrlInput.addEventListener('input', (e) => {
      showImage(e.target.value.trim());
      setPillState(imgPill, 'pending', 'Slika: URL spreman');
    });
  }

  if (imgUploadInput) {
    imgUploadInput.addEventListener('change', (e) => {
      const file = e.target.files?.[0];
      if (file) {
        if (!['image/png', 'image/jpeg'].includes(file.type)) {
          alert('Dozvoljen format slike: PNG ili JPG.');
          imgUploadInput.value = '';
          return;
        }
        showImage(URL.createObjectURL(file));
        setPillState(imgPill, 'ready', 'Slika: fajl spreman');
        fileSelected('image');
      }
    });
  }

  if (audioUrlInput) {
    audioUrlInput.addEventListener('input', (e) => {
      showAudio(e.target.value.trim());
      setPillState(audioPill, 'pending', 'Audio: URL spreman');
    });
  }

  if (audioUploadInput) {
    audioUploadInput.addEventListener('change', (e) => {
      const file = e.target.files?.[0];
      if (file) {
        if (!['audio/mpeg', 'audio/mp3', 'audio/x-m4a', 'audio/m4a'].includes(file.type)) {
          alert('Dozvoljen audio format: MP3 ili M4A.');
          audioUploadInput.value = '';
          return;
        }
        showAudio(URL.createObjectURL(file));
        setPillState(audioPill, 'ready', 'Audio: fajl spreman');
        fileSelected('audio');
      }
    });
  }

  function startProgress() {
    if (statusBar) {
      statusBar.dataset.forceShow = '1';
      statusBar.classList.add('show', 'uploading');
    }
    updateStatusVisibility();
    const imgHas = hasImageSelected();
    const audHas = hasAudioSelected();
    setPillState(imgPill, imgHas ? 'uploading' : 'pending', imgHas ? 'Slika: upload...' : 'Slika: nije odabrana');
    setPillState(audioPill, audHas ? 'uploading' : 'pending', audHas ? 'Audio: upload...' : 'Audio: nije odabrano');
    clearInterval(progressTimer);
    clearInterval(manualProgressTimer);
    console.log('[upload] progress started');
  }

  function finishProgress() {
    if (progressTimer) clearInterval(progressTimer);
    if (manualProgressTimer) clearInterval(manualProgressTimer);
    statusBar?.classList.remove('uploading');
  }

  function fileSelected(type) {
    console.log(`[upload] ${type} file selected`);
    if (statusBar) {
      statusBar.dataset.forceShow = '1';
      statusBar.classList.add('show');
      statusBar.classList.remove('uploading'); // pre-upload state
    }
    const audHas = hasAudioSelected();
    const imgHas = hasImageSelected();
    setPillState(imgPill, imgHas ? 'ready' : 'pending', imgHas ? 'Slika: fajl spreman' : 'Slika: nije odabrana');
    setPillState(audioPill, audHas ? 'ready' : 'pending', audHas ? 'Audio: fajl spreman' : 'Audio: nije odabrano');
    updateStatusVisibility();
    if (imgHas && audHas) {
      setPillState(imgPill, 'ready', 'Slika: fajl spreman');
      setPillState(audioPill, 'ready', 'Audio: fajl spreman');
    }
  }

  if (form) {
    form.addEventListener('submit', (e) => {
      // Let the browser handle the form submit natively (avoids duplicate/incorrect URLs)
      return;

      const formData = new FormData(form);
      // Ensure CSRF token exists in payload
      const tokenInput = form.querySelector('input[name="_token"]');
      const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const csrfToken = tokenInput?.value || metaToken;
      if (csrfToken && !formData.get('_token')) {
        formData.append('_token', csrfToken);
      }
      const xhr = new XMLHttpRequest();
      const methodInput = form.querySelector('input[name="_method"]');
      const method = (methodInput?.value || form.getAttribute('method') || 'POST').toUpperCase();
      const action = form.dataset.update || form.getAttribute('action');
      console.log('[upload] method', method, 'action', action);
      xhr.open(method, action, true);
      // Explicitly send CSRF token with XHR (Laravel reads either header or form body)
      const csrf = formData.get('_token');
      if (csrfToken) {
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
      }

      xhr.onreadystatechange = () => {
        if (xhr.readyState === XMLHttpRequest.DONE) {
          console.log('[upload] done status', xhr.status, 'responseURL', xhr.responseURL);
        }
      };

      xhr.upload.onprogress = (evt) => {
        if (!evt.lengthComputable) return;
        const pct = Math.min(99, (evt.loaded / evt.total) * 100);
        console.log('[upload] progress', pct.toFixed(1) + '%');
      };

      xhr.onload = () => {
        finishProgress();
        if (xhr.status >= 200 && xhr.status < 300) {
            setPillState(imgPill, hasImageSelected() ? 'ready' : 'pending', hasImageSelected() ? 'Slika: uploadano' : 'Slika: nije odabrana');
            setPillState(audioPill, hasAudioSelected() ? 'ready' : 'pending', hasAudioSelected() ? 'Audio: uploadano' : 'Audio: nije odabrano');
            const redirect = form.dataset.redirect || window.location.href;
            window.location.href = redirect;
        } else {
          console.error('[upload] failed', xhr.status, xhr.responseText);
          alert('Upload nije uspio. Pokušaj ponovo.');
          statusBar?.classList.remove('uploading');
          updateStatusVisibility();
        }
      };

      xhr.onerror = () => {
        finishProgress();
        console.error('[upload] network error');
        alert('Greška pri uploadu. Provjeri konekciju.');
        statusBar?.classList.remove('uploading');
        updateStatusVisibility();
      };

      xhr.send(formData);
    });
  }

  window.addEventListener('pagehide', () => {
    finishProgress();
  });

  updateStatusVisibility();

  function filterSubcategories() {
    if (!subcategorySelect) return;
    const selectedCat = categorySelect?.value;
    const options = Array.from(subcategorySelect.options);
    options.forEach(opt => {
      if (!opt.dataset.category) return; // skip placeholder
      const match = !selectedCat || opt.dataset.category === selectedCat;
      opt.hidden = !match;
      if (!match && opt.selected) {
        opt.selected = false;
      }
    });
  }

  if (categorySelect) {
    categorySelect.addEventListener('change', filterSubcategories);
    filterSubcategories();
  }
})();
</script>
@endpush
