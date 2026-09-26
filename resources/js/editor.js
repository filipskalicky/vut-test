import { parseValue } from './datepicker.js';
import EditorJS from '@editorjs/editorjs';
import List from '@editorjs/list';
import { editorJsCs } from './editor-i18n-cs.js';

class ListWithoutChecklist extends List {
    static get toolbox() {
        return List.toolbox.filter((item) => item.data?.style !== 'checklist');
    }

    renderSettings() {
        const label = this.api.i18n.t('Checklist');

        return super.renderSettings().filter((item) => item.label !== label);
    }
}

/**
 * Admin news form: Editor.js limited to paragraph, lists, bold, italic and links.
 * The saved JSON is written into the hidden `content` field on submit.
 */
const holder = document.getElementById('editorjs');
const form = document.querySelector('.js-news-form');
const hidden = document.getElementById('content');

if (holder && form && hidden instanceof HTMLInputElement) {
    let initial;

    try {
        initial = holder.dataset.initial ? JSON.parse(holder.dataset.initial) : undefined;
    } catch {
        initial = undefined;
    }

    const editor = new EditorJS({
        holder: 'editorjs',
        placeholder: 'Napište obsah aktuality…',
        data: initial,
        tools: {
            list: {
                class: ListWithoutChecklist,
                inlineToolbar: true,
                config: {
                    defaultStyle: 'unordered',
                    counterTypes: ['numeric', 'lower-roman', 'upper-roman', 'lower-alpha', 'upper-alpha'],
                },
            },
        },
        inlineToolbar: ['bold', 'italic', 'link'],
        i18n: {
            messages: editorJsCs,
        },
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const afterSaveField = form.querySelector('#after_save');
        const afterSave = event.submitter instanceof HTMLButtonElement
            ? event.submitter.value
            : '';

        if (afterSaveField instanceof HTMLInputElement && (afterSave === 'stay' || afterSave === 'back')) {
            afterSaveField.value = afterSave;
        }

        const output = await editor.save();
        hidden.value = JSON.stringify(output);

        const messages = validateNewsForm(form, output);
        if (messages.length > 0) {
            if (typeof window.showAppToast === 'function') {
                window.showAppToast({ type: 'error', text: messages.join('\n') });
            }

            return;
        }

        HTMLFormElement.prototype.submit.call(form);
    });
}

function datetimeStamp(value) {
    const parsed = parseValue(value);
    if (parsed.dates.length === 0) {
        return null;
    }

    const [year, month, day] = parsed.dates[0].split('-').map(Number);
    const [hours, minutes] = (parsed.clock || '00:00').split(':').map(Number);

    return Date.UTC(year, month - 1, day, hours, minutes);
}

function listHasText(items) {
    if (! Array.isArray(items)) {
        return false;
    }

    return items.some((item) => {
        if (typeof item === 'string' && item.replace(/<[^>]+>/g, '').trim() !== '') {
            return true;
        }

        if (item && typeof item === 'object') {
            const content = String(item.content ?? '').replace(/<[^>]+>/g, '').trim();
            if (content !== '') {
                return true;
            }

            return listHasText(item.items ?? []);
        }

        return false;
    });
}

function editorHasText(document) {
    const blocks = Array.isArray(document?.blocks) ? document.blocks : [];

    return blocks.some((block) => {
        const type = String(block?.type ?? '');
        const data = block?.data && typeof block.data === 'object' ? block.data : {};

        if (type === 'paragraph') {
            return String(data.text ?? '').replace(/<[^>]+>/g, '').trim() !== '';
        }

        return type === 'list' && listHasText(data.items ?? []);
    });
}

function validateNewsForm(form, output) {
    const messages = [];
    const title = form.querySelector('#title');
    const visibleFrom = form.querySelector('#visible_from');
    const visibleTo = form.querySelector('#visible_to');

    if (! (title instanceof HTMLInputElement) || title.value.trim() === '') {
        messages.push('Nadpis je povinný.');
    } else if (title.value.trim().length > 255) {
        messages.push('Nadpis může mít nejvýše 255 znaků.');
    }

    if (! (visibleFrom instanceof HTMLInputElement) || visibleFrom.value.trim() === '') {
        messages.push('Datum a čas, odkdy se má aktualita zobrazovat, jsou povinné.');
    } else if (datetimeStamp(visibleFrom.value) === null) {
        messages.push('Zadejte platné datum a čas.');
    }

    if (visibleTo instanceof HTMLInputElement && visibleTo.value.trim() !== '') {
        const toStamp = datetimeStamp(visibleTo.value);
        const fromStamp = visibleFrom instanceof HTMLInputElement
            ? datetimeStamp(visibleFrom.value)
            : null;

        if (toStamp === null) {
            messages.push('Zadejte platné datum a čas.');
        } else if (fromStamp !== null && toStamp < fromStamp) {
            messages.push('Konec zobrazení nesmí být dřívější než začátek.');
        }
    }

    if (! editorHasText(output)) {
        messages.push('Obsah aktuality je povinný.');
    }

    return messages;
}
