@props(['validationMessage' => null, 'modalLoading' => false])

@php
    $messages = [
        'success' => session('status') ?? session('success'),
        'error' => session('error') ?? $validationMessage,
        'warning' => session('warning'),
        'info' => session('info'),
    ];
    $titles = ['success' => 'Sucesso', 'error' => 'Não foi possível concluir', 'warning' => 'Atenção', 'info' => 'Informação'];
@endphp

@if ($modalLoading)
    <dialog class="jemp-loading-dialog" data-operation-loading aria-label="Carregando">
        <span class="jemp-loading__spinner" aria-hidden="true"></span>
    </dialog>
@endif
<div class="jemp-toast-region" data-toast-region role="region" aria-label="Notificações">
    @unless ($modalLoading)
        <div class="jemp-loading" data-operation-loading role="status" hidden>
            <span class="jemp-loading__spinner" aria-hidden="true"></span>
            Carregando…
        </div>
    @endunless
    @foreach ($messages as $variant => $message)
        @if (filled($message))
            <x-dialogs.alert class="jemp-toast" :variant="$variant" :title="$titles[$variant]" :message="$message" />
        @endif
        <template data-toast-template="{{ $variant }}">
            <x-dialogs.alert class="jemp-toast" :variant="$variant" :title="$titles[$variant]" message="" />
        </template>
    @endforeach
</div>
