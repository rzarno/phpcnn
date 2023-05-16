<?php

namespace service\api;

class DriveApi
{
    private const API_URL = 'https://www.companyhouse.de/phpcnn/script/self-driving/api-predict.php';

    function request(string $imgPath) {
        $headers = [
            'Content-Type: application/json',
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_ENCODING => "",
            CURLOPT_USERAGENT => "RaspberrySelfDrivingCar",
            CURLOPT_AUTOREFERER => 1,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POSTFIELDS => json_encode(['file' => base64_encode(file_get_contents($imgPath))]),
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => $headers
        ];

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, $options);

        $content  = curl_exec($ch);

        curl_close($ch);

        return $content;
    }
}