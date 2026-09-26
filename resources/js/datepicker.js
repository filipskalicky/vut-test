import { Calendar, motion, time } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/index.css';

/**
 * Admin visibility fields: Vanilla Calendar Pro with a 24h clock.
 * The input shows d. m. Y H:i; PHP still accepts ISO as well.
 */
function pad(value) {
    return String(value).padStart(2, '0');
}

function formatCzech(date, clock) {
    const [year, month, day] = date.split('-');

    return `${day}. ${month}. ${year} ${clock || '00:00'}`;
}

export function parseValue(value) {
    const trimmed = value.trim();
    const iso = trimmed.match(/^(\d{4}-\d{2}-\d{2})(?:[ T](\d{2}):(\d{2}))?/);
    if (iso) {
        return {
            dates: [iso[1]],
            clock: iso[2] ? `${iso[2]}:${iso[3]}` : '',
        };
    }

    const czech = trimmed.match(/^(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})(?:\s+(\d{1,2}):(\d{2}))?/);
    if (czech) {
        return {
            dates: [`${czech[3]}-${pad(czech[2])}-${pad(czech[1])}`],
            clock: czech[4] ? `${pad(czech[4])}:${czech[5]}` : '',
        };
    }

    return { dates: [], clock: '' };
}

function writeInput(input, calendar) {
    const date = calendar.context.selectedDates[0];
    const clock = calendar.context.selectedTime;

    input.value = date ? formatCzech(date, clock) : '';
    syncClearButton(input);
}

function syncClearButton(input) {
    const clearBtn = input.parentElement?.querySelector('.input-clear');
    const empty = input.value.trim() === '';

    if (clearBtn instanceof HTMLButtonElement) {
        clearBtn.hidden = empty;
    }

    input.classList.toggle('pr-10', ! empty);
}

document.querySelectorAll('.js-datepicker').forEach((element) => {
    if (! (element instanceof HTMLInputElement)) {
        return;
    }

    const optional = element.classList.contains('js-datepicker-optional');
    const initial = parseValue(element.value);

    if (initial.dates[0]) {
        element.value = formatCzech(initial.dates[0], initial.clock || '00:00');
    }

    const calendar = new Calendar(element, {
        inputMode: true,
        positionToInput: 'auto',
        locale: 'cs',
        firstWeekday: 1,
        selectedTheme: 'light',
        extensions: [motion, time],
        animation: true,
        enableSwipe: true,
        selectionTimeMode: 24,
        timeStepMinute: 1,
        selectedDates: initial.dates,
        selectedTime: initial.clock || undefined,
        enableDateToggle: optional,
        onChangeToInput(self) {
            writeInput(element, self);
        },
    });

    calendar.init();
    syncClearButton(element);

    const clearBtn = element.parentElement?.querySelector('.input-clear');

    if (optional && clearBtn instanceof HTMLButtonElement) {
        clearBtn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            element.value = '';
            calendar.set({ selectedDates: [] }, { dates: true });
            calendar.hide();
            syncClearButton(element);
        });
    }
});
