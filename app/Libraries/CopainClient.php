<?php

namespace App\Libraries;

class CopainClient
{
    private string $cookie;

    private string $url_check_user;
    private string $url_import;
    private string $url_generate_zip;


    public function __construct()
    {
        $config = config('Copain');

        $this->url_check_user   = $config->url_check_user;
        $this->url_import       = $config->url_import_compet;
        $this->url_generate_zip = $config->url_generate_zip;

        $this->cookie = WRITEPATH . 'copain_cookie.txt';
        if (!file_exists($this->cookie)) {
            file_put_contents($this->cookie, '');
        }
        // ⚠️ NE PAS supprimer le cookie ici
    }


    /*
    ======================
    LOGIN
    ======================
    */

    public function autoLogin()
    {
        $config = config('Copain');

        if (!$config->email || !$config->password) {

            throw new \Exception(
                "copain.email / password manquant dans .env"
            );
        }

        return $this->login(
            $config->email,
            $config->password
        );
    }


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
    ======================
    IMPORT
    ======================
    */

    public function importCompetition($ref, $type, $ordre)
    {
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
    ======================
    GENERATE ZIP
    ======================
    */

    public function generateZip($ref, $type)
    {
        $params = http_build_query([
            'ref'  => $ref,
            'type' => $type,
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => $this->url_generate_zip,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS => $params,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_COOKIEJAR => $this->cookie,
            CURLOPT_COOKIEFILE => $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

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

        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }


    /*
    ======================
    DOWNLOAD AVEC COOKIE
    ======================
    */

    public function downloadFile($url, $dest)
    {
        $curl = curl_init();

        $fp = fopen($dest, 'w');

        curl_setopt_array($curl, [

            CURLOPT_URL => $url,

            CURLOPT_FILE => $fp,

            CURLOPT_COOKIEJAR => $this->cookie,
            CURLOPT_COOKIEFILE => $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_USERAGENT =>
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",

            CURLOPT_REFERER =>
            "https://copain.federation-photo.fr/",

        ]);

        curl_exec($curl);

        curl_close($curl);

        fclose($fp);

        return file_exists($dest) && filesize($dest) > 0;
    }


    /*
    ======================
    CURL POST
    ======================
    */

    private function curlPost($url, $params)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => $url,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS =>
            http_build_query($params),

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_COOKIEJAR => $this->cookie,
            CURLOPT_COOKIEFILE => $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_CONNECTTIMEOUT => 10,
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

        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }
}