<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="main-content">
    <div class="container">
        <?php
        $folder =
            'uploads/competitions/' .
            $competition['saison'] . '_' .
            $competition['urs_id'] . '_' .
            $competition['numero'] . '_' .
            $competition['id'];
        ?>

        <div class="competition-header">

            <div class="competition-header-left">

                <h1 class="competition-title">
                    <?= esc($competition['nom']) ?> — Photos
                </h1>

                <div class="competition-count">
                    <?= count($photos) ?>
                </div>

            </div>

            <div class="competition-actions">

                <input type="text" id="filter-ean" placeholder="EAN">
                <input type="text" id="filter-auteur" placeholder="Auteur">
                <input type="text" id="filter-club" placeholder="Club">

            </div>

        </div>


        <div class="photo-grid">

            <?php foreach ($photos as $photo): ?>

                <div class="photo-card"
                    data-ean="<?= esc($photo['ean']) ?>"
                    data-auteur="<?= esc($photo['auteur']) ?>"
                    data-club="<?= esc($photo['club']) ?>">

                    <a href="<?= base_url($folder . '/photos/' . $photo['ean'] . '.jpg') ?>" target="_blank">

                        <img
                            src="<?= base_url($folder . '/photos/' . $photo['ean'] . '.jpg') ?>"
                            loading="lazy"
                            alt="<?= esc($photo['titre']) ?>">

                    </a>

                    <div class="photo-meta">

                        <div class="photo-header">

                            <div class="photo-title">
                                <?= esc($photo['titre']) ?>
                            </div>

                            <?php if (!empty($photo['place'])) : ?>
                                <span class="photo-place">
                                    <?= $photo['place'] ?>°
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="photo-author">
                            <?= esc($photo['auteur']) ?>
                        </div>

                        <div class="photo-club">
                            <?= esc($photo['club']) ?>
                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>
</div>


<style>
    .photos-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        gap: 20px;
    }

    .photos-title {
        margin: 0;
    }

    .photo-filters {
        display: flex;
        gap: 10px;
    }

    .photo-filters input {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        width: 180px;
    }

    .photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .photo-card img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 6px;
    }

    .photo-meta {
        margin-top: 6px;
        font-size: 13px;
    }

    .photo-title {
        font-weight: 600;
    }

    .photo-author {
        color: #444;
    }

    .photo-club {
        color: #777;
        font-size: 12px;
    }
</style>


<script>
    const filterEAN = document.getElementById('filter-ean')
    const filterAuteur = document.getElementById('filter-auteur')
    const filterClub = document.getElementById('filter-club')

    const cards = document.querySelectorAll('.photo-card')

    function filterPhotos() {

        const ean = filterEAN.value.toLowerCase()
        const auteur = filterAuteur.value.toLowerCase()
        const club = filterClub.value.toLowerCase()

        cards.forEach(card => {

            const cardEAN = card.dataset.ean.toLowerCase()
            const cardAuteur = card.dataset.auteur.toLowerCase()
            const cardClub = card.dataset.club.toLowerCase()

            let show = true

            if (ean && !cardEAN.includes(ean)) show = false
            if (auteur && !cardAuteur.includes(auteur)) show = false
            if (club && !cardClub.includes(club)) show = false

            card.style.display = show ? "block" : "none"

        })

    }

    filterEAN.addEventListener('keyup', filterPhotos)
    filterAuteur.addEventListener('keyup', filterPhotos)
    filterClub.addEventListener('keyup', filterPhotos)
</script>

<?= $this->endSection() ?>