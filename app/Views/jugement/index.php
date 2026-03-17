<?= $this->extend('layout/default') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('css/jugement.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="jugement-container">


    <!-- =====================================================
         FILTRES
    ====================================================== -->

    <div class="jugement-filters">

        <!-- Code barre -->

        <div>
            Code barre
            <input type="text" id="filter-ean">
        </div>

        <!-- Passage -->

        <div>
            Passage
            <input type="number" id="filter-passage">
        </div>


        <!-- Etat -->

        <div class="filters-state">

            <label>
                <input type="checkbox" id="filter-pending" class="filter-checkbox" checked>
                Pending
            </label>

            <label>
                <input type="checkbox" id="filter-partial" class="filter-checkbox" checked>
                Partial
            </label>

            <label>
                <input type="checkbox" id="filter-done" class="filter-checkbox" checked>
                Done
            </label>

        </div>


        <!-- Progress -->

        <div class="jugement-progress">

            <?= esc($competition['nom']) ?> —

            Photo
            <span id="photo-position"><?= $position ?></span>
            /
            <span id="photo-total"><?= $total ?></span>

            — passage
            <span id="photo-numero"><?= $photo['passage'] ?></span>

        </div>

    </div>



    <!-- =====================================================
         GRILLE PHOTOS
    ====================================================== -->

    <div class="jugement-grid">

        <?php foreach ($photos as $p): ?>

        <?php

            if ($p['nb_notes'] == 0) {
                $class = 'pending';
            } elseif ($p['nb_notes'] < $nb_juges) {
                $class = 'partial';
            } else {
                $class = 'done';
            }

            ?>

        <div class="photo-tile <?= $class ?>" data-id="<?= $p['id'] ?>" data-ean="<?= $p['ean'] ?>"
            data-passage="<?= $p['passage'] ?>">

            <?= $p['passage'] ?>

        </div>

        <?php endforeach; ?>

    </div>



    <!-- =====================================================
         MAIN
    ====================================================== -->

    <div class="jugement-main">


        <!-- ======================
             PHOTO
        ======================= -->

        <div class="jugement-photo">

            <?php if (!empty($photo)): ?>

            <img src="<?= base_url(
                                'uploads/competitions/' .
                                    $competitionFolder .
                                    '/photos/' .
                                    $photo['ean'] .
                                    '.jpg'
                            ) ?>" class="photo-juge" id="photo-active">

            <?php endif; ?>

        </div>



        <!-- ======================
             NOTES
        ======================= -->

        <div class="jugement-notes">

            <h3>Notes</h3>

            <?php foreach ($juges as $i => $j): ?>

            <div>
                Juge <?= $i + 1 ?> :
                <?= esc($j['nom']) ?>
            </div>

            <input type="number" class="note-input" data-juge="<?= $j['id'] ?>" min="6" max="20">

            <?php endforeach; ?>

            <hr>

            <div>
                Total
                <input type="number" id="total" readonly>
            </div>

        </div>



        <!-- ======================
             INFOS
        ======================= -->

        <div class="jugement-infos">

            <h3>Infos</h3>

            <p>
                <strong>Titre :</strong>
                <span id="photo-titre">
                    <?= $photo['titre'] ?>
                </span>
            </p>

            <p>
                <strong>EAN :</strong>
                <span id="photo-ean">
                    <?= $photo['ean'] ?>
                </span>
            </p>

            <p>
                <strong>Dossier :</strong><br>

                <span class="photo-path">
                    <?= esc($photosPath) ?>
                </span>

            </p>


            <!-- Disqualify -->

            <div class="disqualify-box">

                <button id="btn-disqualify" class="btn-disqualify" onclick="toggleDisqualify()">
                    Disqualifier
                </button>

            </div>

        </div>


    </div>


</div>

<?= $this->endSection() ?>

<script>
console.log("COMPETITION", <?= $competition_id ?>);
</script>

<?= $this->section('scripts') ?>


<!-- =====================================================
     ZOOM OVERLAY
===================================================== -->

<div id="zoomOverlay" class="zoom-overlay">
    <img id="zoomImage">
</div>


<script>
let competition_id = <?= $competition_id ?>;
console.log("competition_id JS =", <?= $competition_id ?>);
let nb_juges = <?= $nb_juges ?>;
let folder = "<?= $competitionFolder ?>";
let base_url = "<?= base_url() ?>";
</script>

<script src="<?= base_url('js/jugement.js') ?>"></script>

<?= $this->endSection() ?>