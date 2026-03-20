<?php

namespace App\Libraries;

use Config\Database;

class CompetitionCleaner
{

    public function deleteCompetition($competition_id)
    {
        $db = Database::connect();

        $tables = [

            'notes',
            'photos',
            'juges',

            'classements',
            'classementclubs',
            'classementauteurs',

            'medailles'

        ];

        foreach ($tables as $table) {

            if ($db->tableExists($table)) {

                $db->table($table)
                    ->where('competitions_id', $competition_id)
                    ->delete();
            }
        }

        // competitions = id

        if ($db->tableExists('competitions')) {

            $db->table('competitions')
                ->where('id', $competition_id)
                ->delete();
        }
    }
}
