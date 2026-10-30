@props(['firstLabel' => 'Acesso', 'secondLabel' => 'Senha'])

@php $secondContent = isset($second) ? $second : ''; @endphp

<section {{ $attributes->class(['jemp-tabbed-dialog']) }}>
    <div class="jemp-tabs" role="tablist" aria-label="Seções do dialog">
        <input type="radio" name="jemp-dialog-tab-{{ $attributes->get('id', 'default') }}" id="jemp-tab-a-{{ $attributes->get('id', 'default') }}" checked>
        <label role="tab" for="jemp-tab-a-{{ $attributes->get('id', 'default') }}">{{ $firstLabel }}</label>
        <input type="radio" name="jemp-dialog-tab-{{ $attributes->get('id', 'default') }}" id="jemp-tab-b-{{ $attributes->get('id', 'default') }}">
        <label role="tab" for="jemp-tab-b-{{ $attributes->get('id', 'default') }}">{{ $secondLabel }}</label>
        <div class="jemp-tabs__panels">
            <div class="jemp-tabs__panel">{{ $slot }}</div>
            <div class="jemp-tabs__panel">{{ $secondContent }}</div>
        </div>
    </div>
</section>