<?php

namespace App\Libraries;

class CopainLegacyReader
{
    private string $cookie;

    public function __construct()
    {
        $this->cookie = WRITEPATH . 'copain_cookie.txt';
    }

    public function getCompetitions($email, $password)
    {
        $config = config('Copain');

        $params = [
            'pass'  => trim($password),
            'date'  => $email,
            'time'  => $password,
            'login' => uniqid()
        ];

        $params2 = http_build_query($params);

        $url = $config->url_check_user;

        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $params2,
            CURLOPT_RETURNTRANSFER => 1,

            CURLOPT_COOKIEJAR => $this->cookie,
            CURLOPT_COOKIEFILE => $this->cookie,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 100,

            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_USERAGENT =>
            "Mozilla/5.0",

        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }
}
