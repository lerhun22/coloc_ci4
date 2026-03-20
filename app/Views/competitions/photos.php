<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="main-content">
    <div class="container">

        <?php

        $folder =
            'uploads/competitions/' .
            $competition['saison'] . '_' .
            str_pad($competition['urs_id'], 2, '0', STR_PAD_LEFT) . '_' .
            $competition['numero'] . '_' .
            $competition['id'];

        ?>
        <div class="competition-toolbar">

            <div class="toolbar-top">

                <h1 class="competition-title">
                    <?= esc($competition['nom']) ?>
                    <span class="competition-count">
                        (<?= count($photos) ?>)
                    </span>
                </h1>

            </div>


            <div class="toolbar-row">

                <div class="toolbar-sort">

                    <button data-sort="saisie">Saisie</button>
                    <button data-sort="passage">Passage</button>
                    <button data-sort="place">Classement</button>
                    <button data-sort="place" data-desc="1">
                        Classement inversé
                    </button>

                </div>


                <div class="toolbar-filters">

                    <input type="text" id="filter-ean" placeholder="EAN">
                    <input type="text" id="filter-auteur" placeholder="Auteur">
                    <input type="text" id="filter-club" placeholder="Club">

                </div>

            </div>

        </div>

        <div class="photo-grid">

            <?php foreach ($photos as $photo): ?>

            <div class="photo-card" data-ean="<?= esc($photo['ean']) ?>" data-auteur="<?= esc($photo['auteur']) ?>"
                data-club="<?= esc($photo['club']) ?>" data-saisie="<?= (int)$photo['saisie'] ?>"
                data-passage="<?= (int)$photo['passage'] ?>" data-place="<?= (int)$photo['place'] ?>">

                <a href="<?= base_url($folder . '/photos/' . $photo['ean'] . '.jpg') ?>" target="_blank">

                    <img src="<?= base_url($folder . '/photos/' . $photo['ean'] . '.jpg') ?>" loading="lazy"
                        alt="<?= esc($photo['titre']) ?>">

                </a>

                <div class="photo-meta">

                    <div class="photo-header">

                        <div class="photo-title">
                            <?= esc($photo['titre']) ?>
                        </div>

                        <?php if (!empty($photo['place'])): ?>

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
.competition-toolbar {
    margin-bottom: 12px;
}

.toolbar-top {
    margin-bottom: 6px;
}

.toolbar-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toolbar-sort {
    display: flex;
    gap: 6px;
}

.toolbar-sort button {
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
}

.toolbar-filters {
    display: flex;
    gap: 8px;
}

.competition-title {
    font-size: 22px;
}

.competition-count {
    color: #666;
}

.competition-title {
    font-size: 22px;
}

.competition-count {
    font-size: 16px;
    color: #666;
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

.photo-header {
    display: flex;
    justify-content: space-between;
}

.photo-place {
    font-weight: bold;
}
</style>



<script>
const filterEAN = document.getElementById('filter-ean')
const filterAuteur = document.getElementById('filter-auteur')
const filterClub = document.getElementById('filter-club')

const grid = document.querySelector('.photo-grid')


function filterPhotos() {

    const ean = filterEAN.value.toLowerCase()
    const auteur = filterAuteur.value.toLowerCase()
    const club = filterClub.value.toLowerCase()

    const cards = document.querySelectorAll('.photo-card')

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



const sortButtons =
    document.querySelectorAll('[data-sort]')


sortButtons.forEach(button => {

    button.addEventListener('click', function() {

        const type = this.dataset.sort
        const desc = this.dataset.desc

        let cards = Array.from(
            grid.querySelectorAll('.photo-card')
        )

        cards.sort((a, b) => {

            const aVal =
                parseInt(a.dataset[type]) || 0

            const bVal =
                parseInt(b.dataset[type]) || 0

            if (desc) {
                return bVal - aVal
            }

            return aVal - bVal

        })

        cards.forEach(card => grid.appendChild(card))

    })

})
</script>

<?= $this->endSection() ?>