<?php
require __DIR__ . '/../../vendor/autoload.php';

use service\api\DriveApi;
use service\raspberry\Camera;
use service\raspberry\Motor;

$camera = new Camera();
$motor = new Motor();
$api = new DriveApi();

$path = __DIR__ . '/sample.jpg';
$debug = 1;

while (1) {
    if (! $debug) {
        $camera->takePhoto($path);
    }

    $response = $api->request($path);
    echo $response;

    match ((int) $response) {
        1 => $motor->forward(),
        2 => $motor->left(),
        3 => $motor->right(),
        default => null,
    };
}