<?php

namespace App\Libraries;

use App\Models\CompetitionModel;

class CompetitionStorage
{
    protected $basePath;

    public function __construct()
    {
        // writable/uploads/competitions/
        $this->basePath = FCPATH . 'uploads/competitions/';
    }


    /*
    =========================
    CREATE FOLDERS
    =========================
    */

    public function createFolders(string $code)
    {
        $path = $this->basePath . $code . '/';

        $folders = [
            '',
            'photos',
            'thumbs',
            'pdf',
            'pte',
            'export',
            'temp'
        ];

        foreach ($folders as $folder) {

            $dir = $path . $folder;

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        return $path;
    }


    /*
    =========================
    PATH
    =========================
    */

    public function getCompetitionPath(string $code)
    {
        return $this->basePath . $code . '/';
    }

    /*
    =========================
    REGISTER DB
    =========================
    */




    /*
    =========================
    GET ID FROM CODE
    =========================
    */

    public function getCompetitionId(string $code)
    {
        $model = new CompetitionModel();

        $row = $model
            ->where('numero', $code)
            ->first();

        return $row['id'] ?? null;
    }
    public function registerCompetition(string $code)
    {
        $model = new CompetitionModel();

        $parts = explode('_', $code);

        if (count($parts) < 4) {
            throw new \Exception("Format competition invalide");
        }

        $saison = (int)$parts[0];
        $urs    = (int)$parts[1];
        $type   = (int)$parts[2];
        $numero = (int)$parts[3];


        $existing = $model
            ->where('id', $numero)
            ->first();

        if ($existing) {
            return $existing['id'];
        }


        /*
    ============================
    VALEURS PAR DEFAUT MODE ZIP
    ============================
    */

        $data = [

            'id' => $numero,

            'numero' => $type,

            'urs_id' => $urs,

            'saison' => $saison,

            'type' => $type,

            'nom' => $code,

            'date_competition' => date('Y-m-d'),

            'max_photos_club' => 999,
            'max_photos_auteur' => 99,

            'param_photos_club' => 0,
            'param_photos_auteur' => 0,

            'quota' => 0,

            'note_min' => 6,
            'note_max' => 20,

            'nb_auteurs_ur_n2' => 3,
            'nb_clubs_ur_n2' => 7,

            'pte' => 0,
            'nature' => 0

        ];

        $model->insert($data);

        return $numero;
    }
}
