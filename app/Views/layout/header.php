<?php

use App\Libraries\CompetitionService;

$activeCompetition = CompetitionService::getActive();

$competitionBadge = '';

if ($activeCompetition) {

    $competitionBadge =
        $activeCompetition['nom'] . ' - ' .
        $activeCompetition['saison'] . ' - ' .
        $activeCompetition['urs_id'] . ' - ' .
        $activeCompetition['numero'] . ' - ' .
        $activeCompetition['id'];
}

?>

<div class="app-header">

    <div class="header-container">

        <div class="header-brand">
            <a href="<?= base_url() ?>">COLOC</a>
        </div>

        <nav class="header-nav">

            <a href="<?= base_url('dashboard') ?>">Accueil</a>
            <a href="<?= base_url('competitions') ?>">Compétitions</a>
            <a href="<?= base_url('photos') ?>">Photos</a>
            <a href="<?= base_url('jugement') ?>">Jugement</a>
            <a href="<?= base_url('export') ?>">Export</a>
            <a href="<?= base_url('suivi') ?>">Suivi</a>

        </nav>

        <div class="header-competition">

            <span class="badge-competition">
                <?= $competitionBadge ?? '' ?>
            </span>

        </div>

    </div>

</div>
