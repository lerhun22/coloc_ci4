<?php

namespace App\Libraries;

use Config\Database;

class CopainImporter
{
    protected $client;

    public function __construct()
    {
        $this->client = new CopainClient();
    }

    public function importCompetition($ref, $type, $ordre, $progress = null)
    {
        $db = Database::connect();

        // 🔥 callback progress
        $update = function ($step, $percent) use ($progress) {
            if ($progress) {
                $progress($step, $percent);
            }
        };

        try {

            log_message('debug', 'IMPORT TYPE = ' . $type);
            log_message('debug', 'IMPORT ID = ' . $ref);

            /*
            =====================
            API CALL
            =====================
            */

            $update('API', 5);

            $response = $this->client->importCompetition(
                $ref,
                $type,
                $ordre
            );

            if (!$response || ($response['code'] ?? 1) != 0) {
                return ['code' => 'IMPORT_ERROR'];
            }

            /*
            =====================
            CLEAN
            =====================
            */

            $update('CLEAN', 10);

            $exists = $db->table('competitions')
                ->where('id', $ref)
                ->countAllResults();

            if ($exists) {
                (new CompetitionCleaner())->deleteCompetition($ref);
            }

            $db->transStart();

            /*
            =====================
            COMPETITION
            =====================
            */

            $update('COMPETITION', 20);

            if (!empty($response['file_compet'])) {

                $compet = json_decode(
                    $this->getJson($response['file_compet']),
                    true
                );

                if ($compet) {

                    $db->table('competitions')->insert([

                        'id' => $compet['id'] ?? $ref,
                        'numero' => $compet['numero'] ?? 0,
                        'type' => $compet['type'] ?? 1,
                        'urs_id' => $compet['urs_id'] ?? null,
                        'saison' => $compet['saison'] ?? '',
                        'nom' => $compet['nom'] ?? '',
                        'date_competition' => $compet['date_competition'] ?? null,

                        // 🔥 AJOUT OBLIGATOIRE
                        'max_photos_club' => $compet['max_photos_club'] ?? 0,
                        'max_photos_auteur' => $compet['max_photos_auteur'] ?? 0,
                        'param_photos_club' => $compet['param_photos_club'] ?? 0,
                        'param_photos_auteur' => $compet['param_photos_auteur'] ?? 0,
                        'quota' => $compet['quota'] ?? 0,
                        'note_min' => $compet['note_min'] ?? 6,
                        'note_max' => $compet['note_max'] ?? 20,
                        'nb_auteurs_ur_n2' => $compet['nb_auteurs_ur_n2'] ?? 0,
                        'nb_clubs_ur_n2' => $compet['nb_clubs_ur_n2'] ?? 0,
                        'pte' => $compet['pte'] ?? 0,
                        'nature' => $compet['nature'] ?? 0,
                    ]);
                }
            }

            /*
            =====================
            CLUBS
            =====================
            */

            $update('CLUBS', 35);

            if (!empty($response['file_club'])) {

                $rows = json_decode(
                    $this->getJson($response['file_club']),
                    true
                );

                foreach ($rows ?? [] as $c) {
                    $db->table('clubs')->ignore(true)->insert($c);
                }
            }

            /*
            =====================
            PARTICIPANTS
            =====================
            */

            $update('PARTICIPANTS', 50);

            if (!empty($response['file_participant'])) {

                $rows = json_decode(
                    $this->getJson($response['file_participant']),
                    true
                );

                foreach ($rows ?? [] as $p) {

                    $db->table('participants')->ignore(true)->insert([
                        'id' => str_replace('-', '', $p['id']),
                        'nom' => $p['nom'] ?? '',
                        'prenom' => $p['prenom'] ?? '',
                        'club_id' => $p['club_id'] ?? null,
                        'competitions_id' => $ref,
                    ]);
                }
            }

            /*
            =====================
            JUGES
            =====================
            */

            $update('JUGES', 65);

            if (!empty($response['file_juge'])) {

                $rows = json_decode(
                    $this->getJson($response['file_juge']),
                    true
                );

                foreach ($rows ?? [] as $j) {

                    $db->table('juges')->ignore(true)->insert([
                        'id' => $j['id'],
                        'nom' => $j['nom'] ?? '',
                        'competitions_id' => $ref,
                    ]);
                }
            }

            /*
            =====================
            PHOTOS
            =====================
            */

            $update('PHOTOS', 80);

            if (!empty($response['file_photos'])) {

                $rows = json_decode(
                    $this->getJson($response['file_photos']),
                    true
                );

                foreach ($rows ?? [] as $p) {
                    $db->table('photos')->insert([

                        'id' => $p['id'],
                        'ean' => $p['ean'],
                        'competitions_id' => $ref,

                        'participants_id' => isset($p['participants_id'])
                            ? str_replace('-', '', $p['participants_id'])
                            : 0,

                        'titre' => html_entity_decode($p['titre'] ?? ''),

                        // 🔥 AJOUT OBLIGATOIRE
                        'statut' => $p['statut'] ?? 0,
                        'saisie' => $p['saisie'] ?? 0,
                        'passage' => $p['passage'] ?? 0,
                        'disqualifie' => $p['disqualifie'] ?? 0,
                    ]);
                }
            }

            /*
            =====================
            NOTES
            =====================
            */

            $update('NOTES', 90);

            if (!empty($response['file_note'])) {

                $rows = json_decode(
                    $this->getJson($response['file_note']),
                    true
                );

                foreach ($rows ?? [] as $n) {

                    $db->table('notes')->insert([
                        'juges_id' => $n['juges_id'],
                        'photos_id' => $n['photos_id'],
                        'note' => $n['note'],
                        'competitions_id' => $ref,
                    ]);
                }
            }

            /*
            =====================
            MEDAILLES
            =====================
            */

            $update('MEDAILLES', 100);

            if (!empty($response['file_medaille'])) {

                $rows = json_decode(
                    $this->getJson($response['file_medaille']),
                    true
                );

                foreach ($rows ?? [] as $m) {

                    $db->table('medailles')->ignore(true)->insert([
                        'id' => $m['id'],
                        'nom' => $m['nom'],
                        'competitions_id' => $ref,
                    ]);
                }
            }

            $db->transComplete();

            log_message('debug', 'IMPORT END');

            return ['code' => 0];
        } catch (\Throwable $e) {

            log_message('error', $e->getMessage());

            return ['code' => 'EXCEPTION'];
        }
    }

    protected function getJson($file)
    {
        return file_get_contents($file);
    }
}