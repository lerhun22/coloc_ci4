<?php

$competitionId = $competitionId ?? null;

?>

<header class="app-header">

    <div class="container header-container">


        <!-- LOGO -->

        <div class="header-brand">
            <a href="<?= site_url('/') ?>">
                COLOC
            </a>
        </div>


        <!-- NAV -->

        <?php $competitionId = $competitionId ?? null; ?>

        <nav class="header-nav">

            <a href="<?= site_url('/') ?>" class="<?= uri_string() == '' ? 'active' : '' ?>">
                Accueil
            </a>

            <a href="<?= site_url('competitions') ?>"
                class="<?= str_starts_with(uri_string(), 'competitions') ? 'active' : '' ?>">
                Compétitions
            </a>


            <!-- PHOTOS -->

            <a href="<?= $competitionId
                            ? site_url('competitions/' . $competitionId . '/photos')
                            : site_url('competitions') ?>"
                class="<?= str_contains(uri_string(), 'photos') ? 'active' : '' ?>">
                Photos
            </a>


            <!-- JUGEMENT -->

            <a href="<?= $competitionId
                            ? site_url('jugement')
                            : site_url('competitions') ?>"
                class="<?= str_contains(uri_string(), 'jugement') ? 'active' : '' ?>">
                Jugement
            </a>


            <!-- EXPORT -->

            <a href="<?= $competitionId
                            ? site_url('export')
                            : site_url('competitions') ?>"
                class="<?= str_contains(uri_string(), 'export') ? 'active' : '' ?>">
                Export
            </a>


            <!-- SUIVI -->

            <a href="<?= site_url('suivi') ?>" class="<?= str_starts_with(uri_string(), 'suivi') ? 'active' : '' ?>">
                Suivi
            </a>

        </nav>


        <!-- BADGE -->

        <div class="header-competition">

            <?php if (!empty($activeCompetition)): ?>

                <span class="badge-competition">

                    <?= esc($activeCompetition['nom']) ?>

                    <span>-
                        <?= $activeCompetition['saison'] ?? '' ?>
                        <?= $activeCompetition['urs_id'] ?? '' ?>
                        <?= $activeCompetition['type'] ?? '' ?>
                        <?= $activeCompetition['id'] ?? '' ?>
                    </span>
                </span>

            <?php else: ?>

                <span class="badge-competition none">
                    Aucune compétition
                </span>

            <?php endif; ?>

        </div>

    </div>

</header>
