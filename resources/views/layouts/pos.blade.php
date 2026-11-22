<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir · {{ \App\Models\Setting::get('store_name', 'Smart Kasir') }}</title>
    
    @include('partials.head-assets')
</head>
<body class="bg-light pos-body">
    <div class="pos-container d-flex">
        {{ $slot }}
    </div>
    @include('partials.page-guide')
</body>
</html>
