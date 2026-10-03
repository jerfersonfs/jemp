@props([
    'month' => 1,
    'year' => 2025,
    'selectedDay' => 5,
    'trigger' => 'Selecionar data',
])

@php
    $monthNames = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
    $firstDate = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
    $firstWeekday = (int) $firstDate->format('N');
    $daysInMonth = (int) $firstDate->format('t');
    $weekdays = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
@endphp

<details {{ $attributes->class(['jemp-date-picker']) }}>
    <summary class="jemp-date-picker__trigger">{{ $trigger }}</summary>
    <section class="jemp-date-picker__panel" aria-label="Calendário">
        <header class="jemp-date-picker__header">
            <button type="button" aria-label="Mês anterior">‹</button>
            <strong>{{ $monthNames[$month] ?? $month }} {{ $year }}</strong>
            <button type="button" aria-label="Próximo mês">›</button>
        </header>
        <div class="jemp-date-picker__grid" role="grid">
            @foreach ($weekdays as $weekday)<span class="jemp-date-picker__weekday" role="columnheader">{{ $weekday }}</span>@endforeach
            @for ($blank = 1; $blank < $firstWeekday; $blank++)<span aria-hidden="true"></span>@endfor
            @for ($day = 1; $day <= $daysInMonth; $day++)
                <button class="jemp-date-picker__day {{ $day === (int) $selectedDay ? 'is-selected' : '' }}" type="button" role="gridcell" aria-pressed="{{ $day === (int) $selectedDay ? 'true' : 'false' }}">{{ $day }}</button>
            @endfor
        </div>
        <footer class="jemp-date-picker__footer"><button type="button">Cancelar</button><button type="button">Aplicar</button></footer>
    </section>
</details>

<style>
    .jemp-date-picker { position: relative; display: inline-block; color: var(--color-caption, #365352); font-family: var(--font-family, sans-serif); }
    .jemp-date-picker__trigger { width: fit-content; list-style: none; cursor: pointer; }
    .jemp-date-picker__trigger::-webkit-details-marker { display: none; }
    .jemp-date-picker__panel { position: absolute; z-index: 30; top: calc(100% + 8px); left: 0; width: 284px; box-sizing: border-box; border: 1px solid color-mix(in srgb, var(--color-border, #000) 16%, white); border-radius: 7px; background: white; padding: 12px; box-shadow: 0 12px 32px rgb(27 64 65 / 16%); }
    .jemp-date-picker__header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; color: var(--color-secondary, #1b4041); font-size: var(--font-size-caption, 12px); }
    .jemp-date-picker__header button, .jemp-date-picker__footer button { border: 0; border-radius: 4px; background: transparent; padding: 6px 8px; color: var(--color-secondary, #1b4041); font: inherit; cursor: pointer; }
    .jemp-date-picker__grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; text-align: center; }
    .jemp-date-picker__weekday { padding: 5px 0; color: var(--color-caption, #365352); font-size: 10px; }
    .jemp-date-picker__day { aspect-ratio: 1; border: 0; border-radius: 50%; background: transparent; color: var(--color-secondary, #1b4041); font: inherit; font-size: 11px; cursor: pointer; }
    .jemp-date-picker__day:hover { background: var(--color-background, #f3f7f8); }
    .jemp-date-picker__day.is-selected { background: var(--color-primary, #0a9680); color: white; }
    .jemp-date-picker__footer { display: flex; justify-content: space-between; border-top: 1px solid var(--color-background, #f3f7f8); margin-top: 8px; padding-top: 8px; }
    .jemp-date-picker__footer button:last-child { background: var(--color-primary, #0a9680); color: white; }
</style>
