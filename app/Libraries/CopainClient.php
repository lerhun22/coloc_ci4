<?php

namespace App\Libraries;

class CopainClient
{

    private string $url_check_user = '';
    private string $url_import = '';
    private string $url_generate_zip = '';

    public function __construct()
    {
        // à adapter plus tard
        $this->url_check_user = config('Copain')->url_check_user;
        $this->url_import = config('Copain')->url_import;
        $this->url_generate_zip = config('Copain')->url_generate_zip;
    }


    /*
    ======================
    GET CONCOURS
    ======================
    */
    public function getConcours($email, $password)
    {

        $params = [
            'pass'  => $password,
            'date'  => $email,
            'time'  => $password,
            'login' => uniqid()
        ];

        return $this->curlPost($this->url_check_user, $params);
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
            'ordre' => $ordre
        ];

        return $this->curlPost($this->url_import, $params);
    }


    /*
    ======================
    ZIP
    ======================
    */
    public function generateZip($ref, $type)
    {

        $params = [
            'ref'  => $ref,
            'type' => $type
        ];

        return $this->curlPost($this->url_generate_zip, $params);
    }



    /*
    ======================
    CURL
    ======================
    */
    private function curlPost($url, $params)
    {

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 100
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            return [
                'code' => 'CURL_ERROR',
                'message' => curl_error($curl)
            ];
        }

        return json_decode($response, true);
    }
}
