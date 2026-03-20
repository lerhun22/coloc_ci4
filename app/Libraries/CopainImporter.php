<?php

namespace App\Libraries;

use Config\Database;
use App\Libraries\CopainClient;
use App\Libraries\CompetitionCleaner;

class CopainImporter
{

    private CopainClient $client;

    public function __construct(CopainClient $client)
    {
        $this->client = $client;
    }


    /*
    ============================
    CURL JSON
    ============================
    */

    private function getJson($url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 60

        ]);

        $data = curl_exec($curl);

        curl_close($curl);

        return $data;
    }


    /*
    ============================
    IMPORT COMPLET
    ============================
    */

    public function importCompetition($ref, $type, $ordre)
    {

        $db = Database::connect();

        $db->transStart();


        /*
        ============================
        APPEL COPAIN
        ============================
        */

        $response = $this->client->importCompetition(
            $ref,
            $type,
            $ordre
        );

        if (!$response || $response['code'] != 0) {

            $db->transRollback();

            return [
                'code' => 'IMPORT_ERROR',
                'response' => $response
            ];
        }


        /*
        ============================
        DELETE
        ============================
        */

        $cleaner = new CompetitionCleaner();

        $cleaner->deleteCompetition($ref);


        /*
        ============================
        COMPETITION
        ============================
        */

        $json_compet = $this->getJson(
            $response['file_compet']
        );

        $compet = json_decode($json_compet, true);


        $competitionData = [

            'id' => $compet['id'],
            'numero' => $compet['numero'],
            'type' => $compet['type'],
            'urs_id' => $compet['urs_id'] ?: null,
            'saison' => $compet['saison'],
            'nom' => $compet['nom'],
            'date_competition' => $compet['date_competition'],

            'max_photos_club' => $compet['max_photos_club'],
            'max_photos_auteur' => $compet['max_photos_auteur'],
            'param_photos_club' => $compet['param_photos_club'],
            'param_photos_auteur' => $compet['param_photos_auteur'],
            'quota' => $compet['quota'],
            'note_min' => $compet['note_min'],
            'note_max' => $compet['note_max'],
            'nb_auteurs_ur_n2' => $compet['nb_auteurs_ur_n2'],
            'nb_clubs_ur_n2' => $compet['nb_clubs_ur_n2'],

            'nature' => $compet['nature']

        ];

        $db->table('competitions')->insert(
            $competitionData
        );


        /*
        ============================
        JUGES
        ============================
        */

        $json_juges = $this->getJson(
            $response['file_juge']
        );

        $juges = json_decode(
            $json_juges,
            true
        );

        foreach ($juges as $j) {

            $db->table('juges')->insert([

                'id' => $j['id'],
                'nom' => $j['nom'],
                'competitions_id' => $ref

            ]);
        }


        /*
        ============================
        PHOTOS
        ============================
        */

        $json_photos = $this->getJson(
            $response['file_photos']
        );

        $photos = json_decode(
            $json_photos,
            true
        );

        foreach ($photos as $p) {

            $db->table('photos')->insert([

                'id' => $p['id'],
                'ean' => $p['ean'],
                'competitions_id' => $ref,
                'participants_id' =>
                str_replace('-', '', $p['participants_id']),
                'titre' =>
                html_entity_decode($p['titre']),
                'statut' => $p['statut'],
                'saisie' => $p['saisie'],
                'passage' => $p['passage'],
                'disqualifie' => $p['disqualifie']

            ]);
        }


        /*
        ============================
        NOTES
        ============================
        */

        $json_notes = $this->getJson(
            $response['file_note']
        );

        $notes = json_decode(
            $json_notes,
            true
        );

        foreach ($notes as $n) {

            $db->table('notes')->insert([

                'juges_id' => $n['juges_id'],
                'photos_id' => $n['photos_id'],
                'note' => $n['note'],
                'competitions_id' => $ref

            ]);
        }


        /*
        ============================
        MEDAILLES
        ============================
        */

        $json_medailles = $this->getJson(
            $response['file_medaille']
        );

        $medailles = json_decode(
            $json_medailles,
            true
        );

        if ($medailles) {

            foreach ($medailles as $m) {

                $db->table('medailles')->insert([

                    'id' => $m['id'],
                    'nom' => $m['nom'],
                    'fpf' => $m['fpf'],
                    'competitions_id' => $ref

                ]);
            }
        }


        /*
        ============================
        DOSSIER CI4
        ============================
        */

        $folder =
            $compet['saison'] . '_' .
            ($compet['urs_id'] ?? 0) . '_' .
            $compet['numero'] . '_' .
            $compet['id'];

        $baseDir =
            FCPATH .
            'uploads/competitions/' .
            $folder;


        if (!is_dir($baseDir)) {

            mkdir($baseDir, 0777, true);

            mkdir($baseDir . '/csv', 0777, true);
            mkdir($baseDir . '/etiquettes', 0777, true);
            mkdir($baseDir . '/pdf', 0777, true);
            mkdir($baseDir . '/photos', 0777, true);
            mkdir($baseDir . '/pte', 0777, true);
            mkdir($baseDir . '/thumbs', 0777, true);
        }


        /*
        ============================
        ZIP PHOTOS
        ============================
        */

        $zipResponse =
            $this->client->generateZip(
                $ref,
                $type
            );


        if ($zipResponse && $zipResponse['code'] == 0) {

            $urlZip =
                $zipResponse['zip_photos'];

            $tmpZip =
                WRITEPATH .
                'zip_' .
                $ref .
                '.zip';


            /*
            DOWNLOAD
            */

            $data =
                file_get_contents($urlZip);

            if ($data === false) {

                $db->transRollback();

                return [
                    'code' =>
                    'ZIP_DOWNLOAD_ERROR'
                ];
            }


            file_put_contents(
                $tmpZip,
                $data
            );


            /*
            UNZIP
            */

            $zip =
                new \ZipArchive();

            if ($zip->open($tmpZip) === true) {

                $zip->extractTo(
                    $baseDir
                );

                $zip->close();
            } else {

                $db->transRollback();

                return [
                    'code' =>
                    'ZIP_OPEN_ERROR'
                ];
            }


            unlink($tmpZip);
        }


        $db->transComplete();

        return ['code' => 0];
    }
}
