<?php $role = session('role') ?? env('app.defaultRole', 'Commissaire'); ?>


<?php

$segments = service('uri')->getSegments();
$competitionId = null;

$competitionId = session('competition_id');

?>


<header class="app-header">

    <div class="header-container">

        <!-- Logo -->
        <div class="header-brand">
            <a href="<?= site_url('/') ?>">COLOC</a>
        </div>

        <!-- Navigation -->
        <nav class="header-nav">

            <a href="<?= site_url('/') ?>" class="<?= uri_string() == '' ? 'active' : '' ?>">
                Accueil
            </a>

            <div class="nav-item">

                <a href="<?= site_url('competitions') ?>"
                    class="<?= str_starts_with(uri_string(), 'competitions') ? 'active' : '' ?>">
                    Compétitions
                </a>

                <div class="dropdown-menu">

                    <?php if (!empty($competitions)): ?>

                    <?php foreach ($competitions as $competition): ?>

                    <a href="<?= site_url('competitions/' . $competition['id']) ?>" class="dropdown-item">

                        <span class="comp-name">
                            <?= esc($competition['nom']) ?>
                        </span>

                        <span class="comp-count">
                            <?= $competition['photo_count'] ?? 0 ?>
                        </span>

                    </a>

                    <?php endforeach; ?>

                    <?php else: ?>

                    <div class="dropdown-empty">
                        Aucune compétition
                    </div>

                    <?php endif; ?>

                    <?php if (!empty($competitions)): ?>

                    <div class="dropdown-divider"></div>

                    <a href="<?= site_url('competitions') ?>" class="dropdown-item dropdown-all">
                        Voir toutes les compétitions →
                    </a>

                    <?php endif; ?>

                </div>

            </div>

            <!-- Jugement -->
            <a href="<?= $competitionId
                            ? site_url('competitions/' . $competitionId . '/jugement')
                            : site_url('competitions') ?>"
                class="<?= str_contains(uri_string(), 'jugement') ? 'active' : '' ?>">
                Jugement
            </a>

            <!-- Résultats -->
            <a href="<?= site_url('resultats') ?>"
                class="<?= str_starts_with(uri_string(), 'resultats') ? 'active' : '' ?>">
                Résultats
            </a>

        </nav>

        <!-- Badge rôle -->
        <div class="header-role">
            <span class="badge-role <?= strtolower($role) ?>">
                <?= esc($role) ?>
            </span>
        </div>

    </div>

</header>