<?php

namespace App\Libraries;

use Config\Database;

class CompetitionCleaner
{
    public function deleteCompetition($id)
    {
        $db = \Config\Database::connect();

        log_message('debug', "DELETE COMPETITION $id");

        $db->table('notes')
            ->where('competitions_id', $id)
            ->delete();

        log_message('debug', "DELETE NOTES");

        $db->table('photos')
            ->where('competitions_id', $id)
            ->delete();

        log_message('debug', "DELETE PHOTOS");

        $db->table('juges')
            ->where('competitions_id', $id)
            ->delete();

        log_message('debug', "DELETE JUGES");

        $db->table('medailles')
            ->where('competitions_id', $id)
            ->delete();

        log_message('debug', "DELETE MEDAILLES");

        $db->table('classements')
            ->where('competitions_id', $id)
            ->delete();

        $db->table('classementclubs')
            ->where('competitions_id', $id)
            ->delete();

        $db->table('classementauteurs')
            ->where('competitions_id', $id)
            ->delete();

        log_message('debug', "DELETE CLASSEMENTS");

        $db->table('competitions')
            ->where('id', $id)
            ->delete();

        log_message('debug', "DELETE COMPET ROW");

        /*
    dossier photos
    */

        $dir =
            FCPATH .
            "uploads/competitions/$id";

        if (is_dir($dir)) {

            helper('filesystem');

            delete_files($dir, true);

            @rmdir($dir);

            log_message(
                'debug',
                "DELETE DIR $dir"
            );
        }
    }


    private function deleteDir($dir)
    {
        foreach (glob($dir . '/*') as $file) {

            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dir);
    }
}
