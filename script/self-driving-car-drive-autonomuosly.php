<?php
require __DIR__.'/../vendor/autoload.php';

use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Matrix\NDArrayPhp;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use service\ImageTransform;
use service\LabelEncoder;
use service\model\Payload;
use service\raspberry\Camera;
use service\raspberry\Motor;
use service\stage\DataImputer;
use service\stage\DriveImageDataProvider;
use service\stage\ImagePreprocesor;

$matrixOperator = new MatrixOperator();
$dataProvider = new DriveImageDataProvider();
$dataImputer = new DataImputer(new ImageTransform(), new LabelEncoder());
$neuralNetworks = new NeuralNetworks($matrixOperator);
$imagePreprocessor = new ImagePreprocesor($matrixOperator);
$camera = new Camera();
$motor = new Motor();

$payload = new Payload(
    $configModelVersion = '1.0',
    $configEpochs = 20,
    $configBatchSize = 64,
    $configImgWidth = 102,
    $configImgHeight = 40,
    $cropFromTop = 40,
    $imputeIterations = 10,
    $configNumImgLayers = 3,
    $configModelFilePath = __DIR__."/../model/image-classification-with-cnn-{$configModelVersion}.model",
    $configClassNames = [1, 2, 3, 4],
    $configUseExistingModel = false
);
$path = __DIR__ . '/photo1.jpg';
$model = $neuralNetworks->models()->loadModel('../model/image-classification-with-cnn-1.0.model');

while (1) {
    $camera->takePhoto(__DIR__ . '/photo1.jpg');

    $images = [$path => 1];
    $data = $dataImputer->imputeData(
        [$path => 1],
        $payload->getConfigImgWidth(),
        $payload->getConfigImgHeight(),
        $payload->getCropFromTop(),
        $payload->getImputeIterations()
    );
    $imgNDArray = new NDArrayPhp(
        $images,
        NDArray::int16,
        [count($images), $payload->getConfigNumImgLayers(), $payload->getConfigImgWidth(), $payload->getConfigImgHeight()]
    );
    $normalizedImages = $imagePreprocessor->flattenAndNormalizeImage($imgNDArray, $payload->getConfigInputShape());


    $predictions = $model->predict($normalizedImages);

    var_dump($predictions);

    switch ($predictions[0]) {
        case 1:
            $motor->forward();
            break;
        case 2:
            $motor->left();
            break;
        case 3:
            $motor->right();
            break;
        default:
            error_log("command not recognized\n");
    }
}