<?php

namespace App\Libraries;

use Config\Database;

class CopainImporter
{

    public function importCompetition($ref, $type, $ordre)
    {

        $db = Database::connect();

        $db->transStart();

        /*
        ici on mettra le portage
        de import_concours.php
        */

        $db->transComplete();

        return [
            'code' => 0
        ];
    }
}
