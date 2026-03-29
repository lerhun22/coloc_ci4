<?php

namespace App\Controllers;

use App\Libraries\CopainLegacyReader;



class Home extends BaseController
{
    private $email;
    private $password;

    public function __construct()
    {
        $email = "c1.rougerie@wanadoo.fr";
        $password = "Cloclo22700";
    }

    public function index()
    {
        return "Home Coloc CI4 OK";
    }

    private function deleteDir($dir)
    {
        if (!is_dir($dir)) return;

        $files = scandir($dir);

        foreach ($files as $file) {

            if ($file == '.' || $file == '..') continue;

            $path = $dir . '/' . $file;

            if (is_dir($path)) {

                $this->deleteDir($path);
            } else {

                unlink($path);
            }
        }

        rmdir($dir);
    }


    public function testCopain()
    {
        $config = config('Copain');

        dd($config);
    }

    public function testCopainApi()
    {
        $reader = new CopainLegacyReader();

        $data = $reader->getCompetitions(
            'c1.rougerie@wanadoo.fr',
            'Cloclo22700'
        );

        dd($data);
    }


    public function testImport()

    {
        $email = "xx";
        $password = "xx";

        /*
    ====================
    legacy login
    ====================
    */

        $legacy = new \App\Libraries\CopainLegacyReader();

        $login = $legacy->getCompetitions(
            $email,
            $password
        );

        dd("LOGIN OK", $login);


        /*
    ====================
    import
    ====================
    */

        $client = new \App\Libraries\CopainClient();

        $importer = new \App\Libraries\CopainImporter(
            $client
        );

        $result = $importer->importCompetition(
            4366,
            'N',
            'non'
        );

        dd($result);
    }

    public function testZip_init()
    {
        $email = "c1.rougerie@wanadoo.fr";
        $password = "Cloclo22700";

        $cookie = WRITEPATH . 'copain_cookie.txt';

        if (file_exists($cookie)) {
            unlink($cookie);
        }

        $legacy = new \App\Libraries\CopainLegacyReader();

        $login = $legacy->getCompetitions(
            $email,
            $password
        );

        if ($login['code'] != 0) {
            dd($login);
        }

        $comp = reset($login['rcompetitions']);

        $client = new \App\Libraries\CopainClient();

        $import = $client->importCompetition(
            $comp['id'],
            $comp['type'] ?? 'O',
            $comp['ordre'] ?? 'non'
        );

        dd($import);
    }



    public function testZip()
    {
        $email = "c1.rougerie@wanadoo.fr";
        $password = "Cloclo22700";


        $cookie = WRITEPATH . 'copain_cookie.txt';

        if (file_exists($cookie)) {
            unlink($cookie);
        }

        /*
    =====================
    LOGIN
    =====================
    */

        $legacy = new \App\Libraries\CopainLegacyReader();

        $login = $legacy->getCompetitions(
            $email,
            $password
        );

        if (!$login || $login['code'] != 0) {
            dd("LOGIN FAIL", $login);
        }


        /*
    =====================
    rcompetitions
    =====================
    */

        $compR = reset($login['rcompetitions']);

        $ref   = $compR['id'];
        $type  = $compR['type'] ?? 'O';
        $ordre = $compR['ordre'] ?? 'non';


        /*
    =====================
    CLIENT
    =====================
    */

        $client = new \App\Libraries\CopainClient();


        /*
    =====================
    IMPORT
    =====================
    */

        $import = $client->importCompetition(
            $ref,
            $type,
            $ordre
        );

        if (!$import || $import['code'] != 0) {
            dd("IMPORT FAIL", $import);
        }


        /*
    =====================
    LIRE file_compet
    =====================
    */

        $json = file_get_contents(
            $import['file_compet']
        );

        $compet = json_decode($json, true);

        if (!$compet) {
            dd("JSON FAIL");
        }


        /*
    =====================
    DOSSIER
    =====================
    */

        $folder =
            $compet['saison'] . '_' .
            str_pad($compet['urs_id'], 2, '0', STR_PAD_LEFT) . '_' .
            $compet['numero'] . '_' .
            $compet['id'];

        $baseDir =
            FCPATH . 'uploads/competitions/' . $folder;


        /*
    =====================
    SUPPRIMER
    =====================
    */

        if (is_dir($baseDir)) {
            $this->deleteDir($baseDir);
        }

        mkdir($baseDir, 0777, true);
        mkdir($baseDir . '/photos', 0777, true);


        /*
    =====================
    ZIP
    =====================
    */

        $zip = $client->generateZip(
            $ref,
            $type
        );

        if (!$zip || $zip['code'] != 0) {
            dd("ZIP FAIL", $zip);
        }


        /*
=====================
DOWNLOAD ZIP
=====================
*/

        $tmpZip =
            WRITEPATH . 'zip_' . $ref . '.zip';

        $ok = $client->downloadFile(
            $zip['zip_photos'],
            $tmpZip
        );

        if (!$ok) {
            dd("DOWNLOAD FAIL");
        }


        /*
=====================
STOP ICI
=====================
*/

        dd(
            "ZIP DOWNLOADED",
            $tmpZip,
            "Dézipper manuellement dans :",
            $baseDir . '/photos'
        );
    }



    /**/

    public function testImportZip()
    {
        $email = "c1.rougerie@wanadoo.fr";
        $password = "Cloclo22700";

        $cookie = WRITEPATH . 'copain_cookie.txt';

        if (file_exists($cookie)) {
            unlink($cookie);
        }

        /*
    LOGIN
    */

        $legacy = new \App\Libraries\CopainLegacyReader();

        $login = $legacy->getCompetitions(
            $email,
            $password
        );

        if (!$login || $login['code'] != 0) {
            dd("LOGIN FAIL", $login);
        }

        /*
    choisir compet
    */

        $comp = reset($login['rcompetitions']);

        $ref   = $comp['id'];
        $type  = $comp['type'] ?? 'O';
        $ordre = $comp['ordre'] ?? 'non';


        /*
    client
    */

        $client = new \App\Libraries\CopainClient();


        /*
    IMPORT
    */

        $import = $client->importCompetition(
            $ref,
            $type,
            $ordre
        );

        if (!$import || $import['code'] != 0) {
            dd("IMPORT FAIL", $import);
        }


        /*
    GENERATE ZIP
    */

        $zip = $client->generateZip(
            $ref,
            $type
        );

        if (!$zip || $zip['code'] != 0) {
            dd("ZIP FAIL", $zip);
        }


        /*
    AFFICHER LIEN
    */

        echo "<h2>ZIP prêt</h2>";

        echo "<a href='" . $zip['zip_photos'] . "' target='_blank'>";
        echo "Télécharger le zip";
        echo "</a>";

        echo "<br><br>";

        echo "Ensuite placer le zip dans /zip et lancer traitement";

        exit;
    }
}