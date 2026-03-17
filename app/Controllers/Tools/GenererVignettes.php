<?php

namespace App\Controllers\Tools;

use App\Controllers\BaseController;

class GenererVignettes extends BaseController
{
    public function index($competition_id = null)
    {
        set_time_limit(0);
        ini_set('memory_limit','1024M');

        if (!$competition_id) {
            echo "Competition non spécifiée";
            return;
        }

        $basePath = FCPATH.'uploads/competitions/';
        $dirs = glob($basePath.'*_'.$competition_id);

        if (!$dirs) {
            echo "Dossier compétition introuvable";
            return;
        }

        $competitionPath = $dirs[0];

        $photosPath = $competitionPath.'/photos/';
        $thumbsPath = $competitionPath.'/thumbs/';

        if (!is_dir($thumbsPath)) {
            mkdir($thumbsPath,0775,true);
        }

        $files = glob($photosPath.'*.jpg');
        sort($files);

        $imageService = \Config\Services::image();

        $batch = 100;
        $processed = 0;
        $generated = 0;

        foreach ($files as $file) {

            $filename = basename($file);

            if (!preg_match('/^[0-9]+\.jpg$/',$filename)) {
                continue;
            }

            $thumbFile = $thumbsPath.$filename;

            if (file_exists($thumbFile)) {
                continue;
            }

            try {

                $imageService
                    ->withFile($file)
                    ->fit(300,300,'center')
                    ->save($thumbFile);

                $imageService->clear();

                echo "✔ $filename<br>";

                $generated++;
                $processed++;

            } catch (\Throwable $e) {

                echo "❌ erreur $filename<br>";
            }

            if ($processed >= $batch) {
                break;
            }

            flush();
        }

        echo "<hr>";
        echo "Batch traité : $processed<br>";
        echo "Vignettes générées : $generated<br>";
    }
}