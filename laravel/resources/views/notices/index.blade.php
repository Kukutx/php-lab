<x-layout title="Notices">
    <h1 class="h3 mb-3">Notices <span class="badge text-bg-secondary">{{ $notices->total() }}</span></h1>

    <form method="GET" action="{{ route('notices.index') }}" class="row g-2 mb-4">
        <div class="col-md-4">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search by title" aria-label="Search by title">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="category" aria-label="Category">
                <option value="">All categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->value }}" @selected($activeCategory === $cat)>{{ $cat->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="status" aria-label="Status">
                <option value="published" @selected($status === 'published')>Published</option>
                <option value="drafts" @selected($status === 'drafts')>Drafts &amp; scheduled</option>
                <option value="all" @selected($status === 'all')>All</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-primary">Filter</button>
        </div>
    </form>

    <div class="row g-3">
        @forelse ($notices as $notice)
            <div class="col-md-6 col-lg-4">
                <article class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <x-category-badge :category="$notice->category" />
                            @if ($notice->isPublished())
                                <small class="text-muted">{{ $notice->published_at->format('d/m/Y') }}</small>
                            @else
                                <span class="badge text-bg-light border">{{ $notice->published_at ? 'Scheduled '.$notice->published_at->format('d/m/Y') : 'Draft' }}</span>
                            @endif
                        </div>
                        <h2 class="h5 card-title">
                            <a class="stretched-link text-decoration-none" href="{{ route('notices.show', $notice) }}">{{ $notice->title }}</a>
                        </h2>
                        <p class="card-text text-muted">{{ Str::limit($notice->body, 110) }}</p>
                    </div>
                </article>
            </div>
        @empty
            <p class="text-muted">No notices found.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notices->links('pagination::bootstrap-5') }}
    </div>
</x-layout>
