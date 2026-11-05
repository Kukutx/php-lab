@props(['title' => 'Notices'])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('notices.index') }}">🏛️ Comune di Esempio <small class="opacity-75 d-none d-sm-inline">· Notices board (Laravel)</small></a>
        <a class="btn btn-light btn-sm" href="{{ route('notices.create') }}">+ New notice</a>
    </div>
</nav>
<main class="container pb-5">
    @session('status')
        <div class="alert alert-success" role="status">{{ $value }}</div>
    @endsession

    {{ $slot }}
</main>
</body>
</html>
