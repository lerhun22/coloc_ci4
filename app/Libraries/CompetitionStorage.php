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

    public function registerCompetition(string $code)
    {
        $model = new CompetitionModel();

        $parts = explode('_', $code);

        if (count($parts) < 4) {
            throw new \Exception("Format competition invalide");
        }

        $saison = $parts[0];
        $urs    = $parts[1];
        $type   = $parts[2];
        $numero = $parts[3];

        // ici id = numero concours

        $existing = $model
            ->where('id', $numero)
            ->first();

        if ($existing) {
            return $existing['id'];
        }

        $data = [

            'id' => $numero,
            'numero' => $type,
            'urs_id' => $urs,
            'saison' => $saison,
            'type' => $type,
            'nom' => $code

        ];

        $model->insert($data);

        return $numero;
    }


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
}
