<?php

namespace App\Controllers;

use App\Controllers\BaseController;

use App\Libraries\CopainClient;
use App\Libraries\CopainImporter;
use App\Libraries\CompetitionService;
use App\Models\CompetitionModel;
use App\Libraries\CopainLegacyReader;

/*
========================================================
IMPORT FROM COPAIN
========================================================

Utilise la structure validée :

CopainClient
CopainImporter
CompetitionStorage

IMPORTANT
Ne pas modifier CopainClient (cookies sensibles)
Ne pas modifier login()

Login automatique via .env

========================================================
*/

class ImportFromCopain extends BaseController
{


    /*
    ========================================================
    FORM
    ========================================================
    */


    public function index()
    {
        $config = config('Copain');

        $reader = new CopainLegacyReader();

        $copains = $reader->getCompetitions(
            $config->email,
            $config->password
        );
        /*
        //echo "<pre>";
        //print_r($copains['competitions']);
        //print_r($copains);
        //exit;


        echo "<pre>";
        print_r($copains);
        print_r($config->email);
        print_r($config->password);

        exit;
*/
        $model = new CompetitionModel();

        $existing = $model->select('id')->findAll();

        $existingIds =
            array_column($existing, 'id');

        return view(
            'import/copain',
            [
                'copains' => $copains,
                'existingIds' => $existingIds
            ] + $this->data
        );
    }



    /*
    ========================================================
    RUN IMPORT COPAIN
    ========================================================
    */

    public function run()

    {
        echo "RUN START";

        $ref =
            $this->request->getPost('ref');

        $type =
            $this->request->getPost('type') ?? 1;

        $ordre =
            $this->request->getPost('ordre') ?? 1;


        if (!$ref) {
            return "ref manquant";
        }


        /*
        CONFIG (.env)
        */

        $config = config('Copain');


        /*
        CLIENT
        */

        $client = new CopainClient();


        /*
        LOGIN
        IMPORTANT
        ne pas modifier login()
        cookie obligatoire
        */

        $client->login(
            $config->email,
            $config->password
        );


        /*
        IMPORTER
        */

        $importer =
            new CopainImporter(
                $client
            );


        $result =
            $importer->importCompetition(
                $ref,
                $type,
                $ordre
            );


        if ($result['code'] != 0) {

            echo "<pre>";
            print_r($result);
            exit;
        }


        /*
        ACTIVE COMPETITION
        */

        CompetitionService::setActive(
            $ref
        );


        return redirect()->to(

            site_url(
                'competitions/'
                    . $ref
                    . '/photos'
            )

        );
    }
}
