let photo_id = null;

let photoIndex = [];
let photoPosition = {};

let overlay;
let zoomImg;

/* =====================================================
   UTILS
===================================================== */

function setText(id, val) {
  let el = document.getElementById(id);
  if (!el) return;

  if (val === undefined || val === null) {
    el.innerText = "";
  } else {
    el.innerText = val;
  }
}
function colorInput(input) {
  let v = parseInt(input.value);

  input.classList.remove("ok");

  if (!isNaN(v) && v >= 6 && v <= 20) {
    input.classList.add("ok");
  }
}

/* =====================================================
   LOAD PHOTO
===================================================== */

function loadPhoto(id) {
  if (!id) return;

  fetch(base_url + "/competitions/" + competition_id + "/jugement/photo/" + id)
    .then((r) => r.json())

    .then((data) => {
      if (!data.photo) return;

      photo_id = data.photo.id;

      /* =====================
         IMAGE
      ===================== */

      let url =
        base_url +
        "uploads/competitions/" +
        folder +
        "/photos/" +
        data.photo.ean +
        ".jpg";

      let img = document.getElementById("photo-active");

      if (img) img.src = url;

      /* =====================
         TEXT
      ===================== */

      setText("photo-titre", data.photo.titre);
      setText("photo-ean", data.photo.ean);
      setText("photo-numero", data.photo.passage);

      /* =====================
         NOTES
      ===================== */

      document.querySelectorAll(".note-input").forEach((input) => {
        input.value = "";
        input.classList.remove("ok");
      });

      if (data.notes) {
        data.notes.forEach((n) => {
          let jugeId = String(n.juges_id);

          document.querySelectorAll(".note-input").forEach((input) => {
            if (String(input.dataset.juge) === jugeId) {
              input.value = n.note;
              colorInput(input);
            }
          });
        });
      }

      calcTotal();
      updateCounter();
      attachZoom();

      /* =====================
         DISQUALIFY
      ===================== */

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

      /* =====================
         ACTIVE + SCROLL
      ===================== */

      setActiveThumb(photo_id);
      scrollToThumb(photo_id);
      updateJudgementCounts;
    });
}

/*
========================
PHOTO ACTIVE
========================
*/

function setActiveThumb(photo_id) {
  document.querySelectorAll(".photo-tile").forEach((el) => {
    el.classList.remove("active");
  });

  let el = document.querySelector('.photo-tile[data-id="' + photo_id + '"]');

  if (el) el.classList.add("active");
}

/*
========================
SCROLL AUTO
========================
*/

function scrollToThumb(photo_id) {
  let el = document.querySelector('.photo-tile[data-id="' + photo_id + '"]');

  if (!el) return;

  el.scrollIntoView({
    behavior: "smooth",
    block: "center",
  });
}

function scrollToThumb(photo_id) {
  let el = document.querySelector('[data-id="' + photo_id + '"]');

  if (!el) return;

  el.scrollIntoView({
    behavior: "smooth",
    block: "center",
  });
}

/* =====================================================
   TOTAL
===================================================== */

function calcTotal() {
  let totalVal = 0;

  document.querySelectorAll(".note-input").forEach((input) => {
    let v = parseInt(input.value);

    if (!isNaN(v)) totalVal += v;
  });

  let totalInput = document.getElementById("total");

  if (totalInput) {
    totalInput.value = totalVal;
  }
}

/* =====================================================
   SAVE NOTE
===================================================== */

function saveNote(juge, val) {
  if (!photo_id) return;

  fetch(base_url + "/competitions/" + competition_id + "/jugement/saveNote", {
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

/* =====================================================
   NOTES EVENTS
===================================================== */

function initNotes() {
  document.querySelectorAll(".note-input").forEach((input) => {
    input.addEventListener("input", () => {
      colorInput(input);

      calcTotal();

      saveNote(input.dataset.juge, input.value);

      updateJudgementProgress();

      //autoNextIfReady();
    });
  });
}

/* =====================================================
   NAVIGATION
===================================================== */

function nextPhoto() {
  if (!photo_id) return;

  let pos = photoPosition[photo_id];

  if (pos === undefined) return;

  let nextPos = pos + 1;

  if (nextPos >= photoIndex.length) nextPos = 0;

  let next = photoIndex[nextPos];

  if (next) loadPhoto(next);
}

function prevPhoto() {
  if (!photo_id) return;

  let pos = photoPosition[photo_id];

  if (pos === undefined) return;

  let prevPos = pos - 1;

  if (prevPos < 0) prevPos = photoIndex.length - 1;

  let prev = photoIndex[prevPos];

  if (prev) loadPhoto(prev);
}

/* =====================================================
   FILTERS
===================================================== */

function refreshFilters() {
  let pendingEl = document.getElementById("filter-pending");
  let partialEl = document.getElementById("filter-partial");
  let doneEl = document.getElementById("filter-done");

  let showPending = pendingEl ? pendingEl.checked : false;
  let showPartial = partialEl ? partialEl.checked : false;
  let showDone = doneEl ? doneEl.checked : false;

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
    eanInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();

        loadFromFilters();
      }
    });
  }

  let passageInput = document.getElementById("filter-passage");

  if (passageInput) {
    passageInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();

        loadFromFilters();
      }
    });
  }

  /* NAV clavier */

  document.addEventListener("keydown", (e) => {
    if (e.key === "ArrowRight") {
      e.preventDefault();
      nextPhoto();
    }

    if (e.key === "ArrowLeft") {
      e.preventDefault();
      prevPhoto();
    }
  });
}

/* =====================================================
   FILTER LOAD
===================================================== */

function loadFromFilters() {
  let eanInput = document.getElementById("filter-ean");
  let passageInput = document.getElementById("filter-passage");

  let ean = "";
  let passage = "";

  if (eanInput && eanInput.value) {
    ean = eanInput.value.trim();
  }

  if (passageInput && passageInput.value) {
    passage = passageInput.value.trim();
  }

  let found = null;

  document.querySelectorAll(".photo-tile").forEach((tile) => {
    if (found) return;

    if (ean && tile.dataset.ean === ean) {
      found = tile.dataset.id;
      return;
    }

    if (passage && tile.dataset.passage === passage) {
      found = tile.dataset.id;
      return;
    }
  });

  rebuildPhotoIndex();

  if (found) {
    loadPhoto(found);
  } else if (photoIndex.length > 0) {
    loadPhoto(photoIndex[0]);
  }
}

/* =====================================================
   DISQUALIFY
===================================================== */

function toggleDisqualify() {
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

/* =====================================================
   INDEX / COUNTER
===================================================== */

function rebuildPhotoIndex() {
  photoIndex = [];
  photoPosition = {};

  document.querySelectorAll(".photo-tile").forEach((tile) => {
    if (tile.style.display === "none") return;

    let id = tile.dataset.id;

    if (!id) return;

    photoPosition[id] = photoIndex.length;

    photoIndex.push(id);
  });

  if (photoIndex.length === 0) {
    console.warn("Index vide");
  }
}

function updateCounter() {
  if (!photo_id) return;

  let pos = photoPosition[photo_id];

  if (pos === undefined) return;

  let total = photoIndex.length;

  let elPos = document.getElementById("photo-position");
  if (elPos) {
    elPos.textContent = pos + 1;
  }

  let elTotal = document.getElementById("photo-total");
  if (elTotal) {
    elTotal.textContent = total;
  }
}

function updateTileState(id, state) {
  let tile = document.querySelector('.photo-tile[data-id="' + id + '"]');

  if (!tile) return;

  tile.classList.remove("pending", "partial", "done");

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

/* =====================================================
   ZOOM
===================================================== */

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

/* =====================================================
   INIT
===================================================== */

document.addEventListener("DOMContentLoaded", () => {
  initZoom();
  initNotes();
  initFilters();
  updateJudgementProgress();

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

  if (photoIndex.length > 0) {
    loadPhoto(photoIndex[0]);
  }
});

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else {
    document.exitFullscreen();
  }
}

/*
========================
PROGRESSION
========================
*/

function updateProgress() {
  if (!photo_id) return;

  let text = document.getElementById("progress-text");
  let bar = document.getElementById("progress-bar-inner");

  if (!text || !bar) return; // DOM pas prêt

  let pos = photoPosition[photo_id];

  if (pos === undefined) return;

  let total = Object.keys(photoPosition).length;

  let current = pos + 1;

  let percent = Math.round((current / total) * 100);

  text.innerText = current + " / " + total + " (" + percent + "%)";

  bar.style.width = percent + "%";
}

function updateJudgementProgress() {
  let total = document.querySelectorAll(".photo-tile").length;

  let done = document.querySelectorAll(".photo-tile.done").length;

  let partial = document.querySelectorAll(".photo-tile.partial").length;

  let pending = document.querySelectorAll(".photo-tile.pending").length;

  let text = document.getElementById("progress-text");

  if (!text) return;

  text.innerText =
    "Jugées : " +
    done +
    " / " +
    total +
    " — partielles : " +
    partial +
    " — restantes : " +
    pending;
}

function updateJudgementCounts() {
  let tiles = document.querySelectorAll(".photo-tile");

  if (!tiles.length) return;

  let total = tiles.length;

  let done = document.querySelectorAll(".photo-tile.done").length;

  let partial = document.querySelectorAll(".photo-tile.partial").length;

  let pending = document.querySelectorAll(".photo-tile.pending").length;

  setText("count-total", total);
  setText("count-done", done);
  setText("count-partial", partial);
  setText("count-pending", pending);
}

window.addEventListener("load", function () {
  setTimeout(() => {
    updateJudgementCounts();
  }, 200);
});
