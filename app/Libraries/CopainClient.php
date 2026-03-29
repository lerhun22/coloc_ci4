<?php

namespace App\Libraries;

class CopainClient
{
    private string $cookie;

    private string $url_check_user;
    private string $url_import;
    private string $url_generate_zip;
    private string $url_liste;


    public function __construct()
    {
        $config = config('Copain');

        $this->url_check_user   = $config->url_check_user;
        $this->url_import       = $config->url_import_compet;
        $this->url_generate_zip = $config->url_generate_zip;
        $this->url_liste        = $config->url_liste_competitions;

        $this->cookie =
            WRITEPATH .
            'copain_cookie.txt';

        if (!file_exists($this->cookie)) {
            file_put_contents(
                $this->cookie,
                ''
            );
        }
    }


    /*
    ===================================
    LOGIN
    ===================================
    */

    public function login($email, $password)
    {
        $params = [

            'pass'  => trim($password),
            'date'  => $email,
            'time'  => $password,
            'login' => uniqid(),

        ];

        return $this->curlPost(
            $this->url_check_user,
            $params
        );
    }


    /*
    ===================================
    IMPORT COMPETITION
    ===================================
    */

    public function importCompetition(
        $ref,
        $type,
        $ordre
    ) {

        $params = [

            'ref'   => $ref,
            'type'  => $type,
            'ordre' => $ordre,

        ];

        return $this->curlPost(
            $this->url_import,
            $params
        );
    }


    /*
    ===================================
    GENERATE ZIP
    ===================================
    */

    public function generateZip(
        $ref,
        $type
    ) {
        $params = [

            'ref'  => $ref,
            'type' => $type,

        ];

        return $this->curlPost(
            $this->url_generate_zip,
            $params
        );
    }


    /*
    ===================================
    DOWNLOAD FILE (ZIP)
    stable gros fichiers
    ===================================
    */

    public function downloadFile(
        $url,
        $dest
    ) {
        set_time_limit(0);

        log_message(
            'debug',
            'DOWNLOAD URL = ' . $url
        );

        $dir = dirname($dest);

        if (!is_dir($dir)) {
            mkdir(
                $dir,
                0777,
                true
            );
        }

        $fp = fopen(
            $dest,
            'wb'
        );

        if (!$fp) {

            log_message(
                'error',
                'FOPEN FAIL ' . $dest
            );

            return false;
        }

        $curl = curl_init($url);

        curl_setopt_array($curl, [

            CURLOPT_FILE => $fp,

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_COOKIEJAR =>
            $this->cookie,

            CURLOPT_COOKIEFILE =>
            $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_TIMEOUT => 0,

            CURLOPT_USERAGENT =>
            "Mozilla/5.0",

            CURLOPT_REFERER =>
            "https://copain.federation-photo.fr/",

        ]);

        curl_exec($curl);

        if (curl_errno($curl)) {

            log_message(
                'error',
                curl_error($curl)
            );

            curl_close($curl);
            fclose($fp);

            return false;
        }

        curl_close($curl);
        fclose($fp);

        clearstatcache(
            true,
            $dest
        );

        if (!file_exists($dest)) {

            log_message(
                'error',
                'ZIP NOT FOUND'
            );

            return false;
        }

        $size =
            filesize($dest);

        log_message(
            'debug',
            'ZIP SIZE = ' . $size
        );

        if ($size < 1000) {

            log_message(
                'error',
                'ZIP TOO SMALL'
            );

            return false;
        }

        return true;
    }


    /*
    ===================================
    REMOTE SIZE
    ===================================
    */

    public function getRemoteFileSize(
        $url
    ) {
        $curl = curl_init($url);

        curl_setopt_array($curl, [

            CURLOPT_NOBODY => true,

            CURLOPT_COOKIEJAR =>
            $this->cookie,

            CURLOPT_COOKIEFILE =>
            $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_RETURNTRANSFER => true,

        ]);

        curl_exec($curl);

        $size =
            curl_getinfo(
                $curl,
                CURLINFO_CONTENT_LENGTH_DOWNLOAD
            );

        curl_close($curl);

        return $size;
    }


    /*
    ===================================
    LISTE COMPETITIONS
    ===================================
    */

    public function getCompetitions()
    {
        $config = config('Copain');

        $url =
            $config->url_json .
            'concours.json';

        $json =
            @file_get_contents($url);

        if (!$json) {

            log_message(
                'error',
                'JSON LIST ERROR'
            );

            return [
                'competitions' => [],
                'rcompetitions' => []
            ];
        }

        $data =
            json_decode(
                $json,
                true
            );

        if (!$data) {

            return [
                'competitions' => [],
                'rcompetitions' => []
            ];
        }

        return $data;
    }

    /*
    ===================================
    CURL POST GENERIC
    ===================================
    */

    private function curlPost($url, $params)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            [

                CURLOPT_URL => $url,

                CURLOPT_POST => true,

                CURLOPT_POSTFIELDS =>
                http_build_query($params),

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_COOKIEJAR =>
                $this->cookie,

                CURLOPT_COOKIEFILE =>
                $this->cookie,

                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,

                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT => 300,

                CURLOPT_FOLLOWLOCATION => true,

                CURLOPT_USERAGENT =>
                "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",

                CURLOPT_REFERER =>
                "https://copain.federation-photo.fr/",

                CURLOPT_HTTPHEADER => [

                    "Accept: */*",
                    "Connection: keep-alive",
                    "Origin: https://copain.federation-photo.fr"

                ],

            ]
        );

        $response = curl_exec($curl);

        if (curl_errno($curl)) {

            log_message(
                'error',
                'CURL ERROR: ' . curl_error($curl)
            );
        }

        curl_close($curl);

        return json_decode($response, true);
    }

    /*
======================
AUTO LOGIN
======================
*/

    public function autoLogin()
    {
        $config = config('Copain');

        if (
            empty($config->email)
            || empty($config->password)
        ) {
            throw new \Exception(
                "Copain email/password manquant"
            );
        }

        return $this->login(
            $config->email,
            $config->password
        );
    }

    public function debugListe()
    {
        $url = $this->url_liste;

        $curl = curl_init($url);

        curl_setopt_array($curl, [

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_COOKIEJAR  => $this->cookie,
            CURLOPT_COOKIEFILE => $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_FOLLOWLOCATION => true,
        ]);

        curl_exec($curl);

        curl_close($curl);

        exit;
    }

    public function fetchCompetitionData(int $id, int $type): array
    {
        $this->autoLogin();

        $res = $this->importCompetition($id, $type, 1);

        if (empty($res) || $res['code'] != 0) {
            throw new \Exception('Import failed');
        }

        return [
            'compet' => json_decode(file_get_contents($res['file_compet']), true),
            'photos' => json_decode(file_get_contents($res['file_photos']), true),
        ];
    }

    public function fetchCompetitionImages(int $id, int $type): string
    {
        $res = $this->generateZip($id, $type);

        return $res['zip_photos'] ?? null;
    }
}