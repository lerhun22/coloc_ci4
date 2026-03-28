<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<h2>Import compétition COPAINS</h2>


<style>
.card-import {

    background: #fff;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 12px;

    box-shadow: 0 2px 6px rgba(0, 0, 0, .1);

}

.card-title {

    font-size: 18px;
    font-weight: bold;

}

.card-info {

    color: #666;
    margin-bottom: 10px;

}

.progress-box {

    height: 18px;
    background: #ddd;
    border-radius: 5px;
    overflow: hidden;
    display: none;
    margin-bottom: 8px;

}

.progress-bar {

    height: 18px;
    width: 0%;
    background: #28a745;
    transition: 0.2s;

}

.progress-text {

    font-size: 12px;
    color: #333;

}

.btn-import {

    padding: 6px 10px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;

}

.btn-view {

    padding: 6px 10px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;

}
</style>



<h3>National</h3>

<?php foreach ($copains['competitions'] as $c): ?>

<?= view('import/card', [
        'c' => $c,
        'label' => 'National'
    ]) ?>

<?php endforeach ?>

<div style="
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:20px;
">

    <h3>Régional</h3>

    <div>

        Filtre UR :

        <button onclick="filterUR('')">Toutes</button>

        <button onclick="filterUR('22')">UR22</button>

        <button onclick="filterUR('12')">UR12</button>

        <button onclick="filterUR('14')">UR14</button>

    </div>

</div>


<?php foreach ($copains['rcompetitions'] as $c): ?>

<?= view('import/card', [
        'c' => $c,
        'label' => 'Régional'
    ]) ?>

<?php endforeach ?>



<script>
let running = null;



function showProgress(id) {

    document
        .querySelector("#progress-" + id)
        .style.display = "block"

    document
        .querySelector("#btn-" + id)
        .style.display = "none"

}


function tick(id) {

    fetch("<?= base_url('import/step') ?>/" + id)

        .then(r => r.json())

        .then(s => {

            let bar =
                document.querySelector(
                    "#bar-" + id
                )

            let text =
                document.querySelector(
                    "#text-" + id
                )

            bar.style.width =
                s.progress + "%"

            text.innerHTML =
                s.step +
                " — " +
                s.progress + "%"


            if (s.status !== "done") {
                setTimeout(
                    () => tick(id),
                    500
                )
            } else {

                text.innerHTML =
                    "Import terminé"

                running = null

                window.location =
                    "<?= base_url('competitions') ?>/" +
                    id +
                    "/photos";

            }

        })

}

function filterByUR(ur) {

    document.querySelectorAll('.card-import').forEach(card => {

        const cardUR = card.dataset.ur;

        // 🔥 conversion string → string (safe)
        if (!ur || cardUR === String(ur)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }

    });
}


function startImport(id, type, folder) {

    if (running) {
        alert("Import déjà en cours");
        return;
    }

    running = id;

    const progressBox = document.getElementById('progress-' + id);
    const text = document.getElementById('text-' + id);

    if (progressBox) progressBox.style.display = 'block';
    if (text) text.innerHTML = 'Import en cours...';

    console.log('IMPORT', id, type, folder);

    let url = "<?= base_url('import/start') ?>/" + id;

    // 🔥 ajout params propre
    url += "?type=" + encodeURIComponent(type);

    if (folder) {
        url += "&folder=" + encodeURIComponent(folder);
    }

    setTimeout(() => {
        window.location = url;
    }, 300);
}
</script>


<?= $this->endSection() ?>