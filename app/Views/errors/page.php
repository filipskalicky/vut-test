<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="card card-full-mobile mx-auto max-w-lg p-8 text-center sm:p-12">
        <p class="page-kicker"><?= (int) $errorCode ?></p>
        <h1 class="page-title mt-5"><?= esc($errorHeading) ?></h1>
        <a href="<?= site_url('/') ?>" class="btn-primary mt-8">
            Zpět na přehled
        </a>
    </div>
<?= $this->endSection() ?>
