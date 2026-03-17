let photo_id = null;

let photoIndex = [];
let photoPosition = {};

let overlay;
let zoomImg;

/* ===========================
   SAFE TEXT
=========================== */

function setText(id, val) {
  let el = document.getElementById(id);

  if (!el) return;

  el.innerText = val ?? "";
}

/* ===========================
   COLOR INPUT
=========================== */

function colorInput(input) {
  let v = parseInt(input.value);

  input.classList.remove("ok");

  if (!isNaN(v) && v >= 6 && v <= 20) {
    input.classList.add("ok");
  }
}

/* ===========================
   LOAD PHOTO
=========================== */

function loadPhoto(id) {
  fetch(base_url + "/competitions/" + competition_id + "/jugement/photo/" + id)
    .then((r) => r.json())

    .then((data) => {
      console.log(data); // ✅ ici OK

      if (!data.photo) return;

      photo_id = data.photo.id;

      // construire URL comme en PHP

      let url =
        base_url +
        "/uploads/competitions/" +
        folder +
        "/photos/" +
        data.photo.ean +
        ".jpg";

      let img = document.getElementById("photo-active");

      if (img) {
        img.src = url;
      }

      // titre

      let elTitre = document.getElementById("photo-titre");
      if (elTitre) {
        elTitre.textContent = data.photo.titre;
      }

      // ean

      let elEan = document.getElementById("photo-ean");
      if (elEan) {
        elEan.textContent = data.photo.ean;
      }

      // passage

      let elNumero = document.getElementById("photo-numero");
      if (elNumero) {
        elNumero.textContent = data.photo.passage;
      }

      setText("photo-titre", data.photo.titre);
      setText("photo-ean", data.photo.ean);
      setText("photo-numero", data.photo.passage);

      /* ======================
   NOTES
====================== */

      document.querySelectorAll(".note-input").forEach((input) => {
        input.value = "";
        input.classList.remove("ok");
      });

      if (data.notes) {
        data.notes.forEach((n) => {
          let jugeId = String(n.juges_id).trim();

          document.querySelectorAll(".note-input").forEach((input) => {
            let idInput = String(input.dataset.juge).trim();

            if (idInput === jugeId) {
              input.value = n.note;

              colorInput(input);
            }
          });
        });
      }

      calcTotal();

      updateCounter();

      attachZoom();

      let btn = document.getElementById("btn-disqualify");

      if (btn) {
        if (data.photo.disqualifie == 1) {
          btn.classList.add("disq");
          btn.textContent = "DISQUALIFIÉE";
        } else {
          btn.classList.remove("disq");
          btn.textContent = "Photo valide";
        }
      }

      let tile = document.querySelector(
        '.photo-tile[data-id="' + photo_id + '"]',
      );

      if (tile) {
        tile.classList.toggle("disq", data.photo.disqualifie == 1);
      }
    });
}

/* ===========================
   TOTAL
=========================== */

function calcTotal() {
  let totalVal = 0;

  document.querySelectorAll(".note-input").forEach((input) => {
    let v = parseInt(input.value);

    if (!isNaN(v)) totalVal += v;
  });

  total.value = totalVal;
}

/* ===========================
   SAVE NOTE
=========================== */

function saveNote(juge, val) {
  if (!photo_id) return;

  fetch(base_url + "/jugement/saveNote", {
    method: "POST",

    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },

    body:
      "photo_id=" +
      photo_id +
      "&juge=" +
      juge +
      "&competition_id=" +
      competition_id +
      "&note=" +
      val,
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.state) {
        updateTileState(photo_id, data.state);
      }
    });
}
/* ===========================
   NOTES EVENTS
=========================== */

function initNotes() {
  document.querySelectorAll(".note-input").forEach((input) => {
    input.addEventListener("input", () => {
      colorInput(input);

      calcTotal();

      saveNote(input.dataset.juge, input.value);
    });
  });
}

/* ===========================
   NAVIGATION
=========================== */
function nextPhoto() {
  if (!photo_id) return;

  let pos = photoPosition[photo_id];

  if (pos === undefined) return;

  let nextPos = pos + 1;

  if (nextPos >= photoIndex.length) {
    nextPos = 0;
  }

  let next = photoIndex[nextPos];

  if (next) loadPhoto(next);
}

function prevPhoto() {
  if (!photo_id) return;

  let pos = photoPosition[photo_id];

  if (pos === undefined) return;

  let prevPos = pos - 1;

  if (prevPos < 0) {
    prevPos = photoIndex.length - 1;
  }

  let prev = photoIndex[prevPos];

  if (prev) loadPhoto(prev);
}

/* ===========================
   FILTRES
=========================== */
function refreshFilters() {
  let showPending = document.getElementById("filter-pending")?.checked;

  let showPartial = document.getElementById("filter-partial")?.checked;

  let showDone = document.getElementById("filter-done")?.checked;

  document.querySelectorAll(".photo-tile").forEach((tile) => {
    let visible = true;

    if (tile.classList.contains("pending") && !showPending) {
      visible = false;
    }

    if (tile.classList.contains("partial") && !showPartial) {
      visible = false;
    }

    if (tile.classList.contains("done") && !showDone) {
      visible = false;
    }

    tile.style.display = visible ? "flex" : "none";
  });

  rebuildPhotoIndex();
}

function initFilters() {
  document.querySelectorAll(".filter-checkbox").forEach((cb) => {
    cb.addEventListener("change", () => {
      clearOtherFilters("");

      refreshFilters();
    });
  });

  let eanInput = document.getElementById("filter-ean");

  if (eanInput) {
    eanInput.addEventListener("input", () => {
      clearOtherFilters("ean");

      refreshFilters();
    });
  }

  let passageInput = document.getElementById("filter-passage");

  if (passageInput) {
    passageInput.addEventListener("input", () => {
      clearOtherFilters("passage");

      refreshFilters();
    });
  }

  document.getElementById("filter-ean")?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      loadFromFilters();
    }
  });

  document
    .getElementById("filter-passage")
    ?.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        loadFromFilters();
      }
    });

  /* ================================
   NAVIGATION CLAVIER
================================ */

  document.addEventListener("keydown", (e) => {
    // flèche droite = photo suivante
    if (e.key === "ArrowRight") {
      e.preventDefault();
      nextPhoto();
    }

    // flèche gauche = photo précédente
    if (e.key === "ArrowLeft") {
      e.preventDefault();
      prevPhoto();
    }

    // entrée = photo suivante (optionnel)
    if (e.key === "Enter") {
      // si on est dans un input → ne pas naviguer
      const tag = document.activeElement.tagName;

      if (tag === "INPUT") return;

      nextPhoto();
    }
  });
}
/* ===========================
   ZOOM
=========================== */

function initZoom() {
  overlay = document.getElementById("zoomOverlay");

  zoomImg = document.getElementById("zoomImage");

  if (!overlay) return;

  overlay.addEventListener("click", closeZoom);
}

function attachZoom() {
  let photo = document.getElementById("photo-active");

  if (!photo) return;

  photo.onclick = function () {
    zoomImg.src = photo.src;

    overlay.classList.add("active");
  };
}

function closeZoom() {
  overlay.classList.remove("active");
}

/* ===========================
   INIT
=========================== */

document.addEventListener("DOMContentLoaded", () => {
  initZoom();
  initNotes();
  initFilters();

  document.querySelectorAll(".photo-tile").forEach((tile) => {
    let id = parseInt(tile.dataset.id);

    tile.onclick = function () {
      clearOtherFilters("");
      refreshFilters();
      loadPhoto(id);
    };
  });

  refreshFilters();

  rebuildPhotoIndex();

  console.log("INDEX", photoIndex);

  if (photoIndex.length > 0) {
    loadPhoto(photoIndex[0]);
  }
});

function loadFromFilters() {
  let ean = document.getElementById("filter-ean")?.value.trim();
  let passage = document.getElementById("filter-passage")?.value.trim();

  let found = null;

  document.querySelectorAll(".photo-tile").forEach((tile) => {
    if (found) return;

    if (ean && tile.dataset.ean === ean) {
      found = tile.dataset.id;
    }

    if (passage && tile.dataset.passage === passage) {
      found = tile.dataset.id;
    }
  });

  rebuildPhotoIndex();

  if (found) {
    loadPhoto(found);
  } else if (photoIndex.length > 0) {
    loadPhoto(photoIndex[0]);
  }
}

function toggleDisqualify() {
  console.log("CLICK DISQUALIFY", photo_id);

  if (!photo_id) return;

  fetch(
    base_url +
      "/competitions/" +
      competition_id +
      "/jugement/disqualify/" +
      photo_id,
  )
    .then((r) => r.json())
    .then((data) => {
      loadPhoto(photo_id);

      if (data.state) {
        updateTileState(photo_id, data.state);
      }
    });
}

function rebuildPhotoIndex() {
  photoIndex = [];
  photoPosition = {};

  document.querySelectorAll(".photo-tile").forEach((tile) => {
    if (tile.style.display === "none") return;

    let id = tile.dataset.id;

    photoPosition[id] = photoIndex.length;
    photoIndex.push(id);
  });
}

function updateCounter() {
  if (!photo_id) return;

  let pos = photoPosition[photo_id];

  if (pos === undefined) return;

  let total = photoIndex.length;

  // position

  let elPos = document.getElementById("photo-position");
  if (elPos) elPos.textContent = pos + 1;

  // total

  let elTotal = document.getElementById("photo-total");
  if (elTotal) elTotal.textContent = total;
}

function updateTileState(id, state) {
  let tile = document.querySelector('.photo-tile[data-id="' + id + '"]');

  if (!tile) return;

  tile.classList.remove("pending");
  tile.classList.remove("partial");
  tile.classList.remove("done");

  tile.classList.add(state);
}

function clearOtherFilters(except) {
  if (except !== "ean") {
    let ean = document.getElementById("filter-ean");
    if (ean) ean.value = "";
  }

  if (except !== "passage") {
    let p = document.getElementById("filter-passage");
    if (p) p.value = "";
  }
}
