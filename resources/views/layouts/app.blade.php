<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title ?? 'JEMP' }}</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>{{ $slot }}</body>

</html>