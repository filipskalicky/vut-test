import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import '../css/app.css';

/**
 * Shared frontend entry. Page-specific behaviour lives in editor.js
 * and is loaded only on the admin form.
 */

function initUserMenu() {
    const root = document.querySelector('[data-user-menu]');
    if (!root) {
        return;
    }

    const toggle = root.querySelector('[data-user-menu-toggle]');
    const panel = root.querySelector('[data-user-menu-panel]');
    if (!toggle || !panel) {
        return;
    }

    const close = () => {
        panel.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
    };

    const open = () => {
        panel.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
    };

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();
        if (panel.classList.contains('hidden')) {
            open();
        } else {
            close();
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });
}

initUserMenu();

function escapeToastHtml(value) {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showAppToast(toast) {
    const isError = toast.type === 'error';
    const text = String(toast.text ?? '').trim();

    if (text === '') {
        return Promise.resolve();
    }

    const header = document.querySelector('header');
    const top = (header instanceof HTMLElement ? header.getBoundingClientRect().bottom : 80) + 12;
    document.documentElement.style.setProperty('--app-toast-top', `${top}px`);

    return Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: false,
        buttonsStyling: false,
        html: escapeToastHtml(text).replace(/\n/g, '<br>'),
        customClass: {
            popup: isError ? 'flash-toast flash-toast-error' : 'flash-toast flash-toast-success',
            htmlContainer: 'flash-toast-text',
        },
        didOpen(popup) {
            const container = popup.closest('.swal2-container');
            if (container instanceof HTMLElement) {
                container.style.setProperty('inset', `${top}px 1rem auto auto`, 'important');
                container.style.setProperty('z-index', '60', 'important');
            }

            popup.style.cursor = 'pointer';
            popup.addEventListener('click', () => {
                Swal.close();
            });
        },
    });
}

function initToasts() {
    const items = [];

    document.querySelectorAll('[data-app-toasts]').forEach((node) => {
        try {
            const parsed = JSON.parse(node.textContent || '[]');
            if (Array.isArray(parsed)) {
                items.push(...parsed);
            }
        } catch {
            // Ignore malformed payloads from the server.
        }
    });

    items.forEach((item, index) => {
        window.setTimeout(() => {
            showAppToast(item);
        }, index * 120);
    });
}

initToasts();

window.showAppToast = showAppToast;

function initLoginValidation() {
    const form = document.querySelector('.js-login-form');
    if (! (form instanceof HTMLFormElement)) {
        return;
    }

    form.addEventListener('submit', (event) => {
        const username = form.querySelector('#username');
        const password = form.querySelector('#password');
        const emptyUser = ! (username instanceof HTMLInputElement) || username.value.trim() === '';
        const emptyPass = ! (password instanceof HTMLInputElement) || password.value === '';

        if (! emptyUser && ! emptyPass) {
            return;
        }

        event.preventDefault();
        showAppToast({ type: 'error', text: 'Vyplňte login i heslo.' });
    });
}

initLoginValidation();

function initConfirmDelete() {
    document.addEventListener('submit', async (event) => {
        const form = event.target instanceof HTMLFormElement ? event.target : null;
        if (form === null || !form.hasAttribute('data-confirm-delete')) {
            return;
        }

        event.preventDefault();

        const result = await Swal.fire({
            title: 'Smazat aktualitu?',
            text: 'Opravdu chcete tuto aktualitu smazat? Tuto akci nelze vrátit zpět.',
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            focusCancel: true,
            confirmButtonText: 'Smazat',
            cancelButtonText: 'Zrušit',
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-2xl font-sans',
                title: 'text-xl font-bold text-gray-900',
                htmlContainer: 'text-sm text-gray-600',
                actions: 'mt-6 flex gap-3',
                confirmButton: 'btn-danger px-5',
                cancelButton: 'btn-secondary',
            },
        });

        if (result.isConfirmed) {
            form.submit();
        }
    });
}

initConfirmDelete();

function closeListingMenus() {
    document.querySelectorAll('[data-listing-menu]').forEach((root) => {
        root.querySelector('[data-listing-menu-panel]')?.classList.add('hidden');
        root.querySelector('[data-listing-menu-toggle]')?.setAttribute('aria-expanded', 'false');
    });
}

function openListingMenu(name) {
    const root = document.querySelector(`[data-listing-menu="${name}"]`);
    const toggle = root?.querySelector('[data-listing-menu-toggle]');
    const panel = root?.querySelector('[data-listing-menu-panel]');
    if (!(toggle instanceof HTMLElement) || !(panel instanceof HTMLElement)) {
        return;
    }

    panel.classList.remove('hidden');
    toggle.setAttribute('aria-expanded', 'true');
}

function initListingSearch() {
    const form = document.querySelector('[data-listing-search]');
    const input = form?.querySelector('input[name="q"]');
    if (!(form instanceof HTMLFormElement) || !(input instanceof HTMLInputElement)) {
        return;
    }

    const delayMs = 400;
    let timer = 0;
    let controller = null;

    const fieldValue = (name) => {
        const field = form.querySelector(`[name="${name}"]`);

        return field instanceof HTMLInputElement || field instanceof HTMLSelectElement
            ? field.value
            : '';
    };

    const listingUrl = () => {
        const url = new URL(form.action || window.location.href, window.location.origin);
        const params = new URLSearchParams();
        const perPage = fieldValue('per_page');
        const query = input.value.trim();
        const sort = fieldValue('sort') || 'published';
        const dir = fieldValue('dir') || 'desc';
        const status = fieldValue('status');

        const defaultPerPage = form.dataset.defaultPerPage || '10';
        if (perPage !== '' && perPage !== defaultPerPage) {
            params.set('per_page', perPage);
        }

        if (query !== '') {
            params.set('q', query);
        }

        if (sort !== 'published') {
            params.set('sort', sort);
        }

        if (dir !== 'desc') {
            params.set('dir', dir);
        }

        if (status !== '') {
            params.set('status', status);
        }

        url.search = params.toString();

        return url;
    };

    let applied = listingUrl().search;

    const applyListing = async (openMenu = '') => {
        const url = listingUrl();
        if (url.search === applied) {
            return;
        }

        const results = document.querySelector('[data-listing-results]');
        if (!(results instanceof HTMLElement)) {
            window.location.assign(url.toString());

            return;
        }

        if (controller !== null) {
            controller.abort();
        }

        controller = new AbortController();
        results.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });

            if (!response.ok) {
                return;
            }

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nextResults = doc.querySelector('[data-listing-results]');
            const nextTools = doc.querySelector('[data-listing-tools]');
            const tools = form.querySelector('[data-listing-tools]');
            const nextPerPage = doc.querySelector('[data-listing-per-page]');
            const perPageRoot = form.querySelector('[data-listing-per-page]');

            if (nextResults === null) {
                return;
            }

            results.replaceWith(nextResults);
            if (tools instanceof HTMLElement && nextTools !== null) {
                tools.replaceWith(nextTools);
            }
            if (perPageRoot instanceof HTMLElement && nextPerPage !== null) {
                perPageRoot.replaceWith(nextPerPage);
            }

            applied = url.search;
            history.replaceState(null, '', url.toString());

            if (openMenu !== '') {
                openListingMenu(openMenu);
            }
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return;
            }
        } finally {
            document.querySelector('[data-listing-results]')?.removeAttribute('aria-busy');
        }
    };

    const scheduleSearch = () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => {
            applyListing();
        }, delayMs);
    };

    const clearBtn = form.querySelector('.input-clear');

    const syncSearchClear = () => {
        const empty = input.value.trim() === '';

        if (clearBtn instanceof HTMLButtonElement) {
            clearBtn.hidden = empty;
        }

        input.classList.toggle('pr-10', ! empty);
    };

    input.addEventListener('input', () => {
        syncSearchClear();
        scheduleSearch();
    });

    if (clearBtn instanceof HTMLButtonElement) {
        clearBtn.addEventListener('click', (event) => {
            event.preventDefault();
            input.value = '';
            syncSearchClear();
            input.focus();
            window.clearTimeout(timer);
            applyListing();
        });
    }

    syncSearchClear();

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        window.clearTimeout(timer);
        applyListing();
    });

    form.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (target === null) {
            return;
        }

        const toggle = target.closest('[data-listing-menu-toggle]');
        if (toggle instanceof HTMLElement) {
            event.preventDefault();
            event.stopPropagation();
            const root = toggle.closest('[data-listing-menu]');
            const panel = root?.querySelector('[data-listing-menu-panel]');
            const willOpen = panel instanceof HTMLElement && panel.classList.contains('hidden');
            closeListingMenus();
            if (willOpen && root instanceof HTMLElement) {
                openListingMenu(root.getAttribute('data-listing-menu') ?? '');
            }

            return;
        }

        const sortButton = target.closest('[data-sort]');
        if (sortButton instanceof HTMLElement) {
            event.preventDefault();
            const field = sortButton.getAttribute('data-sort') ?? 'published';
            const sortInput = form.querySelector('[name="sort"]');
            const dirInput = form.querySelector('[name="dir"]');
            if (!(sortInput instanceof HTMLInputElement) || !(dirInput instanceof HTMLInputElement)) {
                return;
            }

            if (sortInput.value === field) {
                dirInput.value = dirInput.value === 'asc' ? 'desc' : 'asc';
            } else {
                sortInput.value = field;
                dirInput.value = field === 'title' ? 'asc' : 'desc';
            }

            window.clearTimeout(timer);
            applyListing();

            return;
        }

        const perPageButton = target.closest('[data-per-page]');
        if (perPageButton instanceof HTMLElement) {
            event.preventDefault();
            const perPageInput = form.querySelector('[name="per_page"]');
            const value = perPageButton.getAttribute('data-per-page') ?? '';
            if (!(perPageInput instanceof HTMLInputElement) || value === '') {
                return;
            }

            perPageInput.value = value;
            const label = form.querySelector('[data-listing-per-page-value]');
            if (label !== null) {
                label.textContent = value;
            }

            window.clearTimeout(timer);
            applyListing();

            return;
        }

        const statusButton = target.closest('[data-status]');
        if (statusButton instanceof HTMLElement) {
            event.preventDefault();
            const statusInput = form.querySelector('[name="status"]');
            const status = statusButton.getAttribute('data-status') ?? '';
            if (!(statusInput instanceof HTMLInputElement) || status === '') {
                return;
            }

            const selected = statusInput.value.split(',').filter(Boolean);
            statusInput.value = selected.includes(status)
                ? selected.filter((item) => item !== status).join(',')
                : [...selected, status].join(',');

            window.clearTimeout(timer);
            applyListing('filter');
        }
    });

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (target === null || target.closest('[data-listing-menu]')) {
            return;
        }

        closeListingMenus();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeListingMenus();
        }
    });
}

initListingSearch();
