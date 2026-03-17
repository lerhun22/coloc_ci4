<?php

namespace App\Libraries;

use Config\Database;

class CompetitionCleaner
{

    public function deleteCompetition($competition_id)
    {

        $db = Database::connect();

        $tables = [
            'competitions',
            'juges',
            'photos',
            'notes',
            'classement',
            'classementclub',
            'classementauteur',
            'medaille'
        ];

        foreach ($tables as $table) {

            $db->table($table)
                ->where('competitions_id', $competition_id)
                ->delete();
        }

        $db->table('competitions')
            ->where('id', $competition_id)
            ->delete();
    }
}
