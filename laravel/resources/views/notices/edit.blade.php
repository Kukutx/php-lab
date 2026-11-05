<x-layout title="Edit notice">
    <h1 class="h3 mb-3">Edit notice</h1>
    <form method="POST" action="{{ route('notices.update', $notice) }}" class="card card-body shadow-sm" novalidate>
        {{-- HTML forms only support GET/POST; @method adds a hidden _method=PUT field. --}}
        @method('PUT')
        @include('notices._form')
    </form>
</x-layout>
