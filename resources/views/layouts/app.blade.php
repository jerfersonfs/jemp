@props(['title' => 'JEMP', 'page' => null])

<x-layouts.app :title="$title" :page="$page">
    {{ $slot }}
</x-layouts.app>
