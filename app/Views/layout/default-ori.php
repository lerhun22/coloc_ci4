<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>UR22 – COLOC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">

    <?= $this->renderSection('styles') ?>

</head>

<body>

    <div class="app-container">

        <?= $this->include('layout/header') ?>

        <main class="main-content <?= $page ?? '' ?>">

            <div class="page-wrapper">

                <?= $this->renderSection('content') ?>

            </div>

        </main>

        <?= $this->include('layout/footer') ?>

    </div>

    <?= $this->renderSection('scripts') ?>

    <script src="<?= base_url('js/jugement.js') ?>"></script>

</body>

</html>