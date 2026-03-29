<?php

namespace App\Workflows;

use App\Libraries\CompetitionStorage;
use App\Models\PhotoModel;

class ImportWorkflow
{
    protected int $passage = 1;

    public function start(array $data): array
    {
        // 1. création compétition + dossiers
        $context = $this->stepCreateCompetition($data);

        // 2. import images
        $this->stepImportImages($context, $data);

        return [
            'status' => 'ok',
            'competition_id' => $context['competition_id'],
        ];
    }

    /**
     * STEP 1
     */
    protected function stepCreateCompetition(array $data): array
    {
        $storage = new CompetitionStorage();

        // 🔥 nom basé sur données normalisées
        $name = $data['nom'] ?? ('compet_' . $data['compet']['id']);

        // dossiers
        $basePath = $storage->createFolders($name);

        // DB
        log_message('debug', 'CODE=' . print_r($data, true));

        $competitionId = $storage->registerCompetition($data['compet']);

        return [
            'competition_id' => $competitionId,
            'base_path' => $basePath,
            'photos_path' => $basePath . 'photos/',
        ];
    }

    /**
     * STEP 2
     */
    protected function stepImportImages(array $context, array $data): void
    {
        $photosPath = $context['photos_path'];

        if (empty($data['payload']['images'])) {
            return;
        }

        foreach ($data['payload']['images'] as $image) {

            $sourcePath = $image['path'];
            $filename = $image['filename'];

            $dest = $photosPath . $filename;

            // 🔥 important : copy (pas rename pour compatibilité)
            copy($sourcePath, $dest);

            $this->registerPhoto(
                $context['competition_id'],
                $filename
            );
        }
    }

    /**
     * REGISTER PHOTO (repris de ton controller)
     */
    protected function registerPhoto(
        int $competitionId,
        string $filename
    ): void {

        $model = new PhotoModel();

        $name = pathinfo(
            $filename,
            PATHINFO_FILENAME
        );

        // EAN
        $ean = strlen($name) === 12 ? $name : null;

        $participantId =
            $this->getDefaultParticipant();

        $model->insert([

            'ean' => $ean,

            'competitions_id' => $competitionId,

            'participants_id' => $participantId,

            'titre' => $filename,

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

    /**
     * PARTICIPANT DEFAULT (repris simplifié)
     */
    protected function getDefaultParticipant(): string
    {
        $db = \Config\Database::connect();

        $id = '0000';

        $row = $db->table('participants')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($row) {
            return $id;
        }

        $db->table('participants')->insert([
            'id' => $id,
            'urs_id' => 0,
            'clubs_id' => 0,
            'nom' => 'IMPORT',
            'prenom' => 'IMPORT',
            'etat_adhesion' => 1,
            'annee_cotisation' => date('Y')
        ]);

        return $id;
    }
}