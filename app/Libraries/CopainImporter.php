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

        try {

            log_message('debug', "IMPORT START $ref");

            $db->transStart();

            /*
        ============================
        API COPAIN
        ============================
        */

            $response = $this->client->importCompetition(
                $ref,
                $type,
                $ordre
            );

            if (!$response || ($response['code'] ?? 1) != 0) {

                log_message('error', 'IMPORT API ERROR');

                $db->transRollback();

                return [
                    'code' => 'IMPORT_ERROR',
                    'response' => $response
                ];
            }

            log_message('debug', 'API OK -!!!!!');

            /*
        ============================
        DELETE EXISTING
        ============================
        */

            $exists = $db->table('competitions')
                ->where('id', $ref)
                ->countAllResults();

            log_message(
                'debug',
                "CHECK EXISTS $ref = $exists"
            );

            if ($exists) {

                log_message(
                    'debug',
                    "CALL CLEANER $ref"
                );

                $cleaner = new CompetitionCleaner();

                $cleaner->deleteCompetition($ref);
            }

            /*
        ============================
        COMPETITION
        ============================
        */

            if (!empty($response['file_compet'])) {

                $json = $this->getJson(
                    $response['file_compet']
                );

                $compet = json_decode($json, true);

                if (is_array($compet)) {

                    $data = [

                        'id' => $compet['id'] ?? $ref,
                        'numero' => $compet['numero'] ?? null,
                        'type' => $compet['type'] ?? 1,
                        'urs_id' => $compet['urs_id'] ?? null,
                        'saison' => $compet['saison'] ?? null,
                        'nom' => $compet['nom'] ?? '',
                        'date_competition' =>
                        $compet['date_competition'] ?? null,

                        'max_photos_club' =>
                        $compet['max_photos_club'] ?? 0,

                        'max_photos_auteur' =>
                        $compet['max_photos_auteur'] ?? 0,

                        'param_photos_club' =>
                        $compet['param_photos_club'] ?? 0,

                        'param_photos_auteur' =>
                        $compet['param_photos_auteur'] ?? 0,

                        'quota' =>
                        $compet['quota'] ?? 0,

                        'note_min' =>
                        $compet['note_min'] ?? 6,

                        'note_max' =>
                        $compet['note_max'] ?? 20,

                        'nb_auteurs_ur_n2' =>
                        $compet['nb_auteurs_ur_n2'] ?? 0,

                        'nb_clubs_ur_n2' =>
                        $compet['nb_clubs_ur_n2'] ?? 0,

                        'pte' =>
                        $compet['pte'] ?? 0,

                        'nature' =>
                        $compet['nature'] ?? 0,
                    ];

                    $db->table('competitions')->insert($data);
                }
            }

            log_message('debug', 'COMPET OK');


            /*
        ============================
        JUGES
        ============================
        */

            if (!empty($response['file_juge'])) {

                $json = $this->getJson(
                    $response['file_juge']
                );

                $rows = json_decode($json, true);

                if (is_array($rows)) {

                    foreach ($rows as $j) {

                        try {

                            $db->table('juges')->insert([

                                'id' => $j['id'],
                                'nom' => $j['nom'],
                                'competitions_id' => $ref

                            ]);
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }

            log_message('debug', 'JUGES OK');


            /*
        ============================
        PHOTOS
        ============================
        */

            if (!empty($response['file_photos'])) {

                $json = $this->getJson(
                    $response['file_photos']
                );

                $rows = json_decode($json, true);

                if (is_array($rows)) {

                    foreach ($rows as $p) {

                        try {

                            $db->table('photos')->insert([

                                'id' => $p['id'],
                                'ean' => $p['ean'],
                                'competitions_id' => $ref,

                                'participants_id' =>
                                str_replace('-', '', $p['participants_id']),

                                'titre' =>
                                html_entity_decode($p['titre']),

                                'statut' =>
                                $p['statut'] ?? 0,

                                'saisie' =>
                                $p['saisie'] ?? 0,

                                'passage' =>
                                $p['passage'] ?? 0,

                                'disqualifie' =>
                                $p['disqualifie'] ?? 0,
                            ]);
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }

            log_message('debug', 'PHOTOS OK');


            /*
        ============================
        NOTES
        ============================
        */

            if (!empty($response['file_note'])) {

                $json = $this->getJson(
                    $response['file_note']
                );

                $rows = json_decode($json, true);

                if (is_array($rows)) {

                    foreach ($rows as $n) {

                        try {

                            $db->table('notes')->insert([

                                'juges_id' =>
                                $n['juges_id'],

                                'photos_id' =>
                                $n['photos_id'],

                                'note' =>
                                $n['note'],

                                'competitions_id' =>
                                $ref

                            ]);
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }

            log_message('debug', 'NOTES OK');


            /*
        ============================
        MEDAILLES
        ============================
        */

            if (!empty($response['file_medaille'])) {

                $json = $this->getJson(
                    $response['file_medaille']
                );

                $rows = json_decode($json, true);

                if (is_array($rows)) {

                    foreach ($rows as $m) {

                        $exists = $db->table('medailles')
                            ->where('id', $m['id'])
                            ->countAllResults();

                        if (!$exists) {

                            $db->table('medailles')->insert([

                                'id' => $m['id'],
                                'nom' => $m['nom'],
                                'fpf' => $m['fpf'],
                                'competitions_id' => $ref
                            ]);
                        }
                    }
                }
            }

            log_message('debug', 'MEDAILLES OK');
            /*
============================
ZIP DESACTIVE (SAFE)
============================
*/

            log_message(
                'debug',
                'ZIP SKIPPED'
            );

            $db->transComplete();

            log_message('debug', 'IMPORT END');

            return ['code' => 0];
        } catch (\Throwable $e) {

            log_message(
                'error',
                'IMPORT EXCEPTION ' .
                    $e->getMessage()
            );

            return [
                'code' => 'EXCEPTION',
                'msg' => $e->getMessage()
            ];
        }
    }
}
