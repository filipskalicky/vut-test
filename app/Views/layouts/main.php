<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= view('partials/seo_head', ['seo' => $seo ?? null, 'title' => $title ?? null]) ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?= vite('resources/js/app.js') ?>
    <?php if (! empty($includeEditor)): ?>
        <?= vite('resources/js/editor.js') ?>
    <?php endif; ?>
</head>
<body class="relative flex min-h-screen flex-col overflow-x-hidden bg-white font-sans text-gray-900">
    <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-40 left-1/2 h-[500px] w-[500px] -translate-x-1/2 rounded-full bg-primary-100/40 blur-3xl"></div>
        <div class="absolute right-0 top-0 h-[400px] w-[400px] rounded-full bg-accent-200/10 blur-3xl"></div>
    </div>

    <header class="sticky top-0 z-50 w-full border-b border-gray-200 bg-white/80 backdrop-blur-lg">
        <div class="mx-auto flex min-h-16 max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-3 lg:h-20 lg:px-8 lg:py-0">
            <a href="<?= site_url('/') ?>" class="flex items-center gap-2.5">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary-600 text-sm font-bold text-white">A</span>
                <span class="text-lg font-semibold tracking-tight text-gray-900">Aktuality</span>
            </a>
            <nav class="flex flex-wrap items-center gap-4 lg:gap-8" aria-label="Hlavní navigace">
                <a href="<?= site_url('/') ?>" class="nav-link">
                    Přehled
                </a>
                <?php if (session()->get('user_id')): ?>
                    <div class="relative" data-user-menu>
                        <button
                            type="button"
                            class="nav-user"
                            data-user-menu-toggle
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="<?= esc((string) session()->get('username'), 'attr') ?>"
                        >
                            <span class="nav-user-icon" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </span>
                            <span class="nav-user-label hidden sm:inline"><?= esc((string) session()->get('username')) ?></span>
                            <svg class="hidden h-4 w-4 text-gray-400 sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="absolute right-0 top-full z-50 hidden pt-2" data-user-menu-panel>
                            <div class="min-w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                                <a href="<?= site_url('admin/news') ?>" class="menu-item">
                                    Administrace
                                </a>
                                <form method="post" action="<?= site_url('logout') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="back" value="<?= esc((string) current_url(true)) ?>">
                                    <button type="submit" class="menu-item w-full text-left">
                                        Odhlásit se
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= site_url('login') ?>" class="nav-user" aria-label="Přihlásit se">
                        <span class="nav-user-icon" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </span>
                        <span class="nav-user-label hidden sm:inline">Přihlásit se</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="relative z-10 mx-auto w-full max-w-5xl flex-1 px-4 py-12 lg:px-8 lg:py-16">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="relative z-0 mt-auto bg-gray-900">
        <div class="mx-auto max-w-5xl px-4 py-12 lg:px-8 lg:py-16">
            <p class="text-sm text-gray-500">
                © <?= date('Y') ?> Všechna práva vyhrazena.
            </p>
        </div>
    </footer>
    <?php
    $flashToasts = [];

    if ($message = session()->getFlashdata('success')) {
        $flashToasts[] = ['type' => 'success', 'text' => (string) $message];
    }

    if ($message = session()->getFlashdata('error')) {
        $flashToasts[] = ['type' => 'error', 'text' => (string) $message];
    }
    ?>
    <script type="application/json" data-app-toasts><?= json_encode($flashToasts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS) ?></script>
</body>
</html>
