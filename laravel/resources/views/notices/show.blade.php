<x-layout :title="$notice->title">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('notices.index') }}">Notices</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($notice->title, 40) }}</li>
        </ol>
    </nav>

    <article class="card card-body shadow-sm">
        <div class="mb-2">
            <x-category-badge :category="$notice->category" />
            <small class="text-muted ms-2">
                {{ $notice->isPublished() ? 'Published '.$notice->published_at->format('d/m/Y H:i') : 'Draft' }}
            </small>
        </div>
        <h1 class="h3">{{ $notice->title }}</h1>
        {{-- {{ }} escapes HTML (XSS-safe); nl2br runs after escaping. --}}
        <p class="lh-lg">{!! nl2br(e($notice->body)) !!}</p>

        <div class="d-flex gap-2 mt-3">
            <a href="{{ route('notices.edit', $notice) }}" class="btn btn-outline-primary">Edit</a>
            <form method="POST" action="{{ route('notices.destroy', $notice) }}" onsubmit="return confirm('Delete this notice?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">Delete</button>
            </form>
        </div>
    </article>
</x-layout>
