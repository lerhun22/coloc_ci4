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

    public function registerCompetition(array $compet)
    {
        $model = new CompetitionModel();

        $existing = $model
            ->where('id', $compet['id'])
            ->first();

        if ($existing) {
            return $existing['id'];
        }

        $data = [

            'id'     => $compet['id'],      // 🔥 ID réel Copain
            'numero' => $compet['numero'],  // 🔥 vrai numero
            'urs_id' => $compet['urs_id'] ?? null,

            'saison' => $compet['saison'],
            'type'   => $compet['type'] ?? 0,

            'nom' => $compet['nom'],        // 🔥 vrai nom

            'date_competition' => $compet['date_competition'] ?? date('Y-m-d'),

            'max_photos_club' => $compet['max_photos_club'] ?? 999,
            'max_photos_auteur' => $compet['max_photos_auteur'] ?? 99,

            'param_photos_club' => $compet['param_photos_club'] ?? 0,
            'param_photos_auteur' => $compet['param_photos_auteur'] ?? 0,

            'quota' => $compet['quota'] ?? 0,

            'note_min' => $compet['note_min'] ?? 6,
            'note_max' => $compet['note_max'] ?? 20,

            'nb_auteurs_ur_n2' => $compet['nb_auteurs_ur_n2'] ?? 3,
            'nb_clubs_ur_n2' => $compet['nb_clubs_ur_n2'] ?? 7,

            'pte' => 0,
            'nature' => $compet['nature'] ?? 0
        ];

        $model->insert($data);

        return $compet['id'];
    }
}