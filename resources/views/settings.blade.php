<x-layouts.app title="Parametrização · JEMP" page="settings">
    <div class="page-head mb-5" @if(session('stock_settings_saved')) data-stock-settings-saved @endif>
        <div>
            <h1>Parametrização</h1>
            <p class="sub">Configurações válidas para toda a empresa.</p>
        </div>
    </div>

    <x-cards.panel class="max-w-2xl" title="Estoque baixo" description="O alerta considera o saldo de cada produto por armazém e condição.">
        <x-slot:header><x-layout.icon name="cube" /></x-slot:header>
        <form class="grid gap-5" method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')
            <div class="max-w-sm">
                <x-inputs.input name="low_stock_threshold" label="Limite de estoque baixo (unidades)"
                    type="number" min="0" max="2147483647" step="1" required :value="$lowStockThreshold"
                    hint="Saldos iguais ou inferiores a este limite aparecem nos alertas de estoque." />
            </div>
            <div class="flex justify-end">
                <x-buttons.button type="submit">Salvar limite</x-buttons.button>
            </div>
        </form>
    </x-cards.panel>

    <x-cards.panel class="max-w-2xl mt-5" title="Condições de venda"
        description="Marque as condições do inventário que permitem saída por venda e entram no alerta de estoque crítico.">
        <form class="grid gap-5" method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PUT')
            <fieldset class="grid gap-2">
                <legend class="sr-only">Condições permitidas para venda</legend>
                <input type="hidden" name="edit_conditions" value="1">
                @foreach ($conditions as $condition => $saleable)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="saleable_conditions[]" value="{{ $condition }}"
                            @checked(old('edit_conditions') ? in_array($condition, (array) old('saleable_conditions', []), true) : $saleable)>
                        {{ $condition }}
                    </label>
                @endforeach
                @error('saleable_conditions.*') <p role="alert">{{ $message }}</p> @enderror
            </fieldset>
            <div class="flex justify-end">
                <x-buttons.button type="submit">Salvar condições</x-buttons.button>
            </div>
        </form>
    </x-cards.panel>
</x-layouts.app>
