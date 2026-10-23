const localizedNumberPattern = /^([^\d]*)([\d.,]+)([^\d]*)$/u;

function parseLocalizedValue(text) {
    const match = text.match(localizedNumberPattern);

    if (!match) {
        return null;
    }

    const [, prefix, numberText, suffix] = match;
    const normalizedNumber = numberText.replaceAll('.', '').replace(',', '.');
    const target = Number(normalizedNumber);

    if (!Number.isFinite(target)) {
        return null;
    }

    const decimalSeparatorIndex = numberText.lastIndexOf(',');
    const fractionDigits = decimalSeparatorIndex === -1 ? 0 : numberText.length - decimalSeparatorIndex - 1;
    const numberFormatter = new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: fractionDigits,
        maximumFractionDigits: fractionDigits,
    });

    return {
        prefix,
        suffix,
        target,
        fractionDigits,
        numberFormatter,
    };
}

function animateHomeStatistics() {
    const statistics = document.querySelector('[data-count-up-group]');

    if (!statistics || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const values = [...statistics.querySelectorAll('.stat-card__value')]
        .map((element) => ({ element, parsed: parseLocalizedValue(element.textContent.trim()) }))
        .filter(({ parsed }) => parsed !== null && parsed.target > 0);

    if (values.length === 0) {
        return;
    }

    const duration = 850;
    const startTime = performance.now();

    values.forEach(({ element, parsed }) => {
        element.setAttribute('data-count-up', '');
        element.setAttribute('aria-live', 'off');
        element.textContent = `${parsed.prefix}${parsed.numberFormatter.format(0)}${parsed.suffix}`;
    });

    function updateValues(currentTime) {
        const progress = Math.min((currentTime - startTime) / duration, 1);
        const easedProgress = 1 - ((1 - progress) ** 3);

        values.forEach(({ element, parsed }) => {
            const currentValue = parsed.target * easedProgress;
            const scale = 10 ** parsed.fractionDigits;
            const displayedValue = parsed.fractionDigits === 0
                ? Math.floor(currentValue)
                : Math.round(currentValue * scale) / scale;

            element.textContent = `${parsed.prefix}${parsed.numberFormatter.format(displayedValue)}${parsed.suffix}`;
        });

        if (progress < 1) {
            window.requestAnimationFrame(updateValues);
        }
    }

    window.requestAnimationFrame(updateValues);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', animateHomeStatistics, { once: true });
} else {
    animateHomeStatistics();
}
