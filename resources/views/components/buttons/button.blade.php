@props(['variant' => 'primary'])

<style>
    .btn {
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
    }

    .btn-primary {
        background: #009688;
        color: white;
    }

    .btn-secondary {
        background: #e8ffff;
        color: #009688;
    }
</style>
<!--Ao importar, definir qual variant vai usar, primary ou secondary-->

<button class="btn btn-{{ $variant }}">
    {{ $slot }}
</button>