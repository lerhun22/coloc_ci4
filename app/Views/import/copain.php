<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>

<div class="container">

    <h1>Import COPAIN</h1>


    <div class="competition-list">


        <!-- ========================= -->
        <!-- NATIONAL -->
        <!-- ========================= -->

        <h2>National</h2>


        <?php if (!empty($copains['competitions'])): ?>

            <?php foreach ($copains['competitions'] as $c): ?>

                <form method="post" action="<?= site_url('import/copain/run') ?>" class="competition-card">

                    <input type="hidden" name="ref" value="<?= $c['id'] ?>">

                    <input type="hidden" name="type" value="1">

                    <input type="hidden" name="ordre" value="1">


                    <!-- ACTION -->

                    <div class="competition-actions">

                        <button type="submit" class="btn-action">
                            IMPORT
                        </button>

                    </div>


                    <!-- TITLE -->

                    <div class="competition-title">

                        <?= esc($c['nom']) ?>

                        <div class="competition-date">

                            National —
                            Saison <?= esc($c['saison']) ?>
                            —
                            #<?= esc($c['id']) ?>

                        </div>

                    </div>


                </form>

            <?php endforeach; ?>

        <?php endif; ?>



        <!-- ========================= -->
        <!-- REGIONAL -->
        <!-- ========================= -->

        <h2>Régional</h2>


        <?php if (!empty($copains['rcompetitions'])): ?>

            <?php foreach ($copains['rcompetitions'] as $c): ?>

                <form method="post" action="<?= site_url('import/copain/run') ?>" class="competition-card">

                    <input type="hidden" name="ref" value="<?= $c['id'] ?>">

                    <input type="hidden" name="type" value="1">

                    <input type="hidden" name="ordre" value="1">


                    <!-- ACTION -->

                    <div class="competition-actions">

                        <button type="submit" class="btn-action">
                            IMPORT
                        </button>

                    </div>


                    <!-- TITLE -->

                    <div class="competition-title">

                        <?= esc($c['nom']) ?>

                        <div class="competition-date">

                            Régional —
                            Saison <?= esc($c['saison']) ?>
                            —
                            UR<?= esc($c['urs_id']) ?>
                            —
                            #<?= esc($c['id']) ?>

                        </div>

                    </div>


                </form>

            <?php endforeach; ?>

        <?php endif; ?>


    </div>

</div>

<?= $this->endSection() ?>
