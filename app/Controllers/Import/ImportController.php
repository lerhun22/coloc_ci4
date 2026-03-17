<?php

namespace App\Controllers;

use App\Libraries\CopainClient;
use App\Libraries\CopainImporter;

class ImportController extends BaseController
{

    /*
    ============================
    LISTE DES CONCOURS COPAIN
    ============================
    */
    public function getConcours()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $client = new CopainClient();

        $result = $client->getConcours($email, $password);

        return $this->response->setJSON($result);
    }


    /*
    ============================
    IMPORT CONCOURS
    ============================
    */
    public function import()
    {
        $ref   = $this->request->getPost('ref');
        $type  = $this->request->getPost('type');
        $ordre = $this->request->getPost('ordre');

        $importer = new CopainImporter();

        $result = $importer->importCompetition($ref, $type, $ordre);

        return $this->response->setJSON($result);
    }


    /*
    ============================
    GENERATE ZIP
    ============================
    */
    public function generateZip()
    {
        $ref  = $this->request->getPost('ref');
        $type = $this->request->getPost('type');

        $client = new CopainClient();

        $result = $client->generateZip($ref, $type);

        return $this->response->setJSON($result);
    }
}
