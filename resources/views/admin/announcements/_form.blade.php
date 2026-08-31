{{--
    Shared create/edit form.
    $action = form target URL, $post = Announcement being edited (or null to create).
--}}
<form method="POST" action="{{ $action }}">
    @csrf
    @isset($post)
        @method('PUT')
    @endisset

    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input id="title" name="title" value="{{ old('title', $post->title ?? '') }}"
               class="form-control @error('title') is-invalid @enderror" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="body" class="form-label">Message</label>
        <textarea id="body" name="body" rows="7"
                  class="form-control @error('body') is-invalid @enderror"
                  required>{{ old('body', $post->body ?? '') }}</textarea>
        <div class="form-text">Line breaks are preserved exactly as you type them.</div>
        @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row">
        <div class="col-sm-6 mb-3">
            <label for="category" class="form-label">
                Category <span class="text-muted fw-normal">(optional)</span>
            </label>
            <select id="category" name="category" class="form-select @error('category') is-invalid @enderror">
                <option value="">— None —</option>
                @foreach (['News', 'Advisory', 'Notice'] as $option)
                    <option value="{{ $option }}"
                        {{ old('category', $post->category ?? '') === $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-sm-6 mb-3">
            <label for="published_at" class="form-label">Publish date</label>
            <input id="published_at" type="date" name="published_at"
                   value="{{ old('published_at', isset($post->published_at) ? $post->published_at->format('Y-m-d') : now()->format('Y-m-d')) }}"
                   class="form-control @error('published_at') is-invalid @enderror">
            <div class="form-text">A future date keeps it hidden until then.</div>
            @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="form-check mb-2">
        <input type="checkbox" name="is_published" id="is_published" value="1"
               class="form-check-input"
               {{ old('is_published', $post->is_published ?? true) ? 'checked' : '' }}>
        <label for="is_published" class="form-check-label">
            Published <span class="text-muted">(uncheck to save as a draft)</span>
        </label>
    </div>

    <div class="form-check mb-4">
        <input type="checkbox" name="is_pinned" id="is_pinned" value="1"
               class="form-check-input"
               {{ old('is_pinned', $post->is_pinned ?? false) ? 'checked' : '' }}>
        <label for="is_pinned" class="form-check-label">
            Pin to the top of the homepage
        </label>
    </div>

    <button class="btn btn-primary w-100">
        {{ isset($post) ? 'Save changes' : 'Post announcement' }}
    </button>
</form>
