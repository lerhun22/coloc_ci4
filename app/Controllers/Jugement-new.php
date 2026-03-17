<?php

namespace App\Controllers;

use App\Models\PhotoModel;
use App\Models\JugementModel;
use App\Models\CompetitionModel;

class Jugement extends BaseController
{
    protected $photoModel;
    protected $jugementModel;
    protected $competitionModel;

    public function __construct()
    {
        $this->photoModel = new PhotoModel();
        $this->jugementModel = new JugementModel();
        $this->competitionModel = new CompetitionModel();
    }

    /* ===============================
       PAGE PRINCIPALE
    =============================== */

    public function index($competition_id) {}

    /* ===============================
       API PHOTO
    =============================== */

    public function getPhoto() {}

    public function nextPhoto() {}

    public function prevPhoto() {}

    /* ===============================
       API NOTES
    =============================== */

    public function saveNote() {}

    /* ===============================
       FILTRES
    =============================== */

    public function getPhotoFromFilters() {}
}