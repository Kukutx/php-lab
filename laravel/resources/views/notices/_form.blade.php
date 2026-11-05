{{-- Shared by create and edit. old() refills the form after a validation error. --}}
@csrf

<div class="mb-3">
    <label for="title" class="form-label">Title *</label>
    <input id="title" name="title" class="form-control @error('title') is-invalid @enderror"
           value="{{ old('title', $notice->title) }}">
    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="category" class="form-label">Category *</label>
    <select id="category" name="category" class="form-select @error('category') is-invalid @enderror">
        <option value="">Choose…</option>
        @foreach ($categories as $cat)
            <option value="{{ $cat->value }}" @selected(old('category', $notice->category?->value) === $cat->value)>{{ $cat->label() }}</option>
        @endforeach
    </select>
    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="body" class="form-label">Text *</label>
    <textarea id="body" name="body" rows="6" class="form-control @error('body') is-invalid @enderror">{{ old('body', $notice->body) }}</textarea>
    @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="published_at" class="form-label">Publish at <small class="text-muted">(empty = draft, future date = scheduled)</small></label>
    <input id="published_at" name="published_at" type="datetime-local"
           class="form-control @error('published_at') is-invalid @enderror"
           value="{{ old('published_at', $notice->published_at?->format('Y-m-d\TH:i')) }}">
    @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div>
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('notices.index') }}" class="btn btn-link">Cancel</a>
</div>
