<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Libraries\CompetitionStorage;
use App\Libraries\CompetitionService;
use App\Models\PhotoModel;

use ZipArchive;

/*
========================================================
IMPORT CONTROLLER
========================================================

Mode autonome club (sans COPAINS)

Fonctions :

- Import ZIP simple
- Création compétition
- Création dossiers
- Scan images
- Enregistrement photos
- Participant placeholder

Extensions prévues :

- Import IPTC
- Import COPAINS (autre controller)
- Import CSV
- Import JSON

========================================================
*/

class Import extends BaseController
{

    private $zipDir;

    private $passage = 1;


    public function __construct()
    {
        /*
        Dossier des ZIP à importer
        */

        $this->zipDir =
            FCPATH . 'uploads/zip/';
    }



    /*
    ========================================================
    PAGE IMPORT
    ========================================================
    */

    public function index()
    {
        $files = glob(
            $this->zipDir . '*.zip'
        );

        $list = [];

        foreach ($files as $f) {

            $list[] = basename($f);
        }

        $data = [];

        $data['zipFiles'] = $list;

        return view(
            'import/index',
            $data
        );
    }



    /*
    ========================================================
    RUN IMPORT ZIP
    ========================================================

    Mode club autonome

    - crée compétition
    - crée dossiers
    - extrait ZIP
    - enregistre photos
    */

    public function run($file)
    {
        $path =
            $this->zipDir . $file;

        if (!file_exists($path)) {

            return redirect()->to('/import');
        }

        $name =
            basename($file, '.zip');


        /*
    ==========================
    DELETE SI EXISTE
    ==========================
    */

        $db = \Config\Database::connect();

        $row = $db->table('competitions')
            ->where('nom', $name)
            ->get()
            ->getRowArray();

        if ($row) {

            $competitionId = $row['id'];

            $cleaner =
                new \App\Libraries\CompetitionCleaner();

            $cleaner->deleteCompetition(
                $competitionId
            );
        }


        /*
    ==========================
    IMPORT
    ==========================
    */

        $competitionId =
            $this->importOneZip($path);


        CompetitionService::setActive(
            $competitionId
        );


        return redirect()->to(

            site_url(
                'competitions/'
                    . $competitionId
                    . '/photos'
            )

        );
    }



    /*
    ========================================================
    IMPORT ZIP
    ========================================================
    */

    private function importOneZip($file)
    {
        $name =
            basename($file, '.zip');


        /*
    =========================
    EXTRAIRE ID DU ZIP
    =========================
    */

        $parts =
            explode('_', $name);

        $competitionIdFromZip =
            end($parts);


        /*
    =========================
    DELETE SI EXISTE
    =========================
    */

        $db =
            \Config\Database::connect();

        $row =
            $db->table('competitions')
            ->where('id', $competitionIdFromZip)
            ->get()
            ->getRowArray();

        if ($row) {

            $cleaner =
                new \App\Libraries\CompetitionCleaner();

            $cleaner->deleteCompetition(
                $competitionIdFromZip
            );
        }


        /*
    =========================
    STORAGE
    =========================
    */

        $storage =
            new CompetitionStorage();


        /*
    Création dossiers
    */

        $basePath =
            $storage->createFolders(
                $name
            );


        /*
    Enregistrement DB
    */

        $competitionId =
            $storage->registerCompetition(
                $name
            );


        $tempPath =
            $basePath . 'temp/';

        $photosPath =
            $basePath . 'photos/';


        /*
    UNZIP
    */

        $zip = new ZipArchive;

        if ($zip->open($file) === TRUE) {

            $zip->extractTo(
                $tempPath
            );

            $zip->close();
        } else {

            throw new \Exception(
                "Erreur ouverture ZIP"
            );
        }


        /*
    SCAN IMAGES
    */

        $files =
            $this->scanImages(
                $tempPath
            );


        /*
    IMPORT PHOTOS
    */

        foreach ($files as $f) {

            $filename =
                basename($f);

            $dest =
                $photosPath
                . $filename;

            rename(
                $f,
                $dest
            );

            $this->registerPhoto(
                $competitionId,
                $filename,
                $name
            );
        }


        return $competitionId;
    }

    private function scanImages($dir)
    {
        $result = [];

        $items =
            scandir($dir);

        foreach ($items as $item) {

            /*
            Ignore fichiers système
            */

            if (
                $item == '.'
                || $item == '..'
                || $item[0] == '.'
                || str_starts_with($item, '._')
                || $item == 'Thumbs.db'
                || $item == 'desktop.ini'
            ) {
                continue;
            }

            $path =
                $dir . '/'
                . $item;

            if (is_dir($path)) {

                $result =
                    array_merge(
                        $result,
                        $this->scanImages(
                            $path
                        )
                    );
            } else {

                if (
                    preg_match(
                        '/\.(jpg|jpeg|png)$/i',
                        $item
                    )
                ) {
                    $result[] = $path;
                }
            }
        }

        return $result;
    }



    /*
    ========================================================
    REGISTER PHOTO
    ========================================================
    */

    private function registerPhoto(
        $competitionId,
        $filename,
        $code
    ) {

        $model = new PhotoModel();

        $name = pathinfo(
            $filename,
            PATHINFO_FILENAME
        );

        /*
        Vérification EAN (si présent)
        */

        if (strlen($name) != 12) {

            $ean = null;
        } else {

            $ean = $name;
        }


        $participantId =
            $this->getDefaultParticipant(
                $competitionId,
                $code
            );


        $model->insert([

            'ean' => $ean,

            'competitions_id'
            => $competitionId,

            'participants_id'
            => $participantId,

            'titre'
            => $filename,

            'statut' => 0,

            'place' => 0,

            'note_totale' => 0,

            'saisie' => 0,

            'retenue' => 0,

            'medailles_id' => null,

            'passage' => $this->passage,

            'disqualifie' => 0

        ]);

        $this->passage++;
    }



    /*
    ========================================================
    DEFAULT PARTICIPANT
    ========================================================
    */

    private function getDefaultParticipant(
        $competitionId,
        $code
    ) {

        $db =
            \Config\Database::connect();

        $id = '0000';


        $row =
            $db->table('participants')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($row) {
            return $id;
        }


        $db->table('participants')
            ->insert([

                'id' => $id,

                'urs_id' => 0,

                'clubs_id' => 0,

                'nom' => 'IMPORT',

                'prenom' => 'IMPORT',

                'etat_adhesion' => 1,

                'annee_cotisation' =>
                date('Y')

            ]);

        return $id;
    }
}
