<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="mx-auto max-w-md">
        <form method="post" action="<?= site_url('login') ?>" class="js-login-form card space-y-5 p-8" novalidate>
            <h1 class="page-title">Přihlášení</h1>
            <?= csrf_field() ?>

            <div>
                <label for="username" class="label">Login</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= esc(old('username', '')) ?>"
                    autocomplete="username"
                    required
                    class="input"
                >
            </div>

            <div>
                <label for="password" class="label">Heslo</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                    class="input"
                >
            </div>

            <button type="submit" class="btn-primary w-full">
                Přihlásit se
            </button>
        </form>
    </div>
<?= $this->endSection() ?>
