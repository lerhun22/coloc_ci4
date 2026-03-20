<?php

namespace App\Services;

use App\Libraries\CopainLegacyReader;
use App\Models\CompetitionModel;

class CopainImportService
{
    protected $reader;
    protected $competitionModel;

    public function __construct()
    {
        $this->reader = new CopainLegacyReader();
        $this->competitionModel = new CompetitionModel();
    }

    public function importCompetitions($email, $password)
    {
        $data = $this->reader->getCompetitions($email, $password);

        if ($data['code'] != 0) {
            return false;
        }

        $count = 0;

        if (!empty($data['competitions'])) {

            foreach ($data['competitions'] as $c) {

                $this->competitionModel->save([
                    'id'     => $c['id'],
                    'nom'    => $c['nom'],
                    'saison' => $c['saison'],
                    'urs_id' => $c['urs_id'] ?? null,
                    'type'   => 'N'
                ]);

                $count++;
            }
        }

        if (!empty($data['rcompetitions'])) {

            foreach ($data['rcompetitions'] as $c) {

                $this->competitionModel->save([
                    'id'     => $c['id'],
                    'nom'    => $c['nom'],
                    'saison' => $c['saison'],
                    'urs_id' => $c['urs_id'],
                    'type'   => 'R'
                ]);

                $count++;
            }
        }

        return $count;
    }
}
