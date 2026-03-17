<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="main-content">

<div class="container">

    <h1 class="page-title">Compétitions</h1>

    <div class="competition-list">

    <?php foreach ($competitions_list as $competition): ?>

        <div class="competition-card"
        onclick="window.location='<?= site_url('competitions/'.$competition['id']) ?>'">

        <div class="competition-left">

            <h2 class="competition-title">
                <?= esc($competition['nom']) ?>
            </h2>

            <div class="competition-date">
                <?= esc($competition['date_competition']) ?>
            </div>

        </div>

        <div class="competition-right">

            <div class="competition-stats">
                <?= $competition['photo_count'] ?? 0 ?> photos • 
                <?= $competition['author_count'] ?? 0 ?> auteurs
            </div>

            <div class="competition-stats">
                <?= $competition['club_count'] ?? 0 ?> clubs • 
                <?= $competition['federation_count'] ?? 0 ?> individuels
            </div>

            <div class="competition-stats-avg">
                Ø <?= $competition['avg_photos_per_author'] ?? 0 ?> photos / auteur • 
                Ø <?= $competition['avg_photos_per_club'] ?? 0 ?> photos / club
            </div>

        </div>

        <div class="competition-arrow">
            →
        </div>

    </div>
        

    <?php endforeach; ?>

    </div>
</div>


</div>

<?= $this->endSection() ?>