<!DOCTYPE html>
<html lang="pt-BR">

@props([
    'title' => 'JEMP',
    'page' => null,
])

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1b4041">
    <title>{{ $title }}</title>
    <script>
        try {
            if (localStorage.getItem('jemp-sidebar-collapsed') === 'true' && !matchMedia('(max-width: 900px)').matches) {
                document.documentElement.classList.add('jemp-sidebar-precollapsed');
            }
        } catch (error) {
            // Keep the default expanded layout when storage is unavailable.
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @if ($page)
        <x-layout.app-shell :page="$page">
            {{ $slot }}
        </x-layout.app-shell>
    @else
        {{ $slot }}
    @endif
</body>

</html>
