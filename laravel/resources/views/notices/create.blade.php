<x-layout title="New notice">
    <h1 class="h3 mb-3">New notice</h1>
    <form method="POST" action="{{ route('notices.store') }}" class="card card-body shadow-sm" novalidate>
        @include('notices._form')
    </form>
</x-layout>
