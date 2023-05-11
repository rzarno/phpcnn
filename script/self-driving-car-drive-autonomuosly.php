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

$testMode = 1; //disable for raspberry

$payload = new Payload(
    $configModelVersion = '1.0',
    $configEpochs = 20,
    $configBatchSize = 64,
    $configImgWidth = 102,
    $configImgHeight = 40,
    $cropFromTop = 40,
    $imputeIterations = 1,
    $configNumImgLayers = 3,
    $configModelFilePath = __DIR__."/../model/image-classification-with-cnn-{$configModelVersion}.model",
    $configClassNames = [1, 2, 3, 4],
    $configUseExistingModel = false
);
$path = __DIR__ . '/sample.jpg';
$model = $neuralNetworks->models()->loadModel('../model/image-classification-with-cnn-1.0.model');

while (1) {

    if (! $testMode) {
        $camera->takePhoto($path);
    }
    $images = [$path => 1];
    $data = $dataImputer->imputeData(
        $images,
        $payload->getConfigImgWidth(),
        $payload->getConfigImgHeight(),
        $payload->getCropFromTop(),
        $payload->getImputeIterations()
    );
    $imgNDArray = new NDArrayPhp(
        $data[0],
        NDArray::int16,
        [count($data[0]), $payload->getConfigNumImgLayers(), $payload->getConfigImgWidth(), $payload->getConfigImgHeight()]
    );
    $normalizedImages = $imagePreprocessor->flattenAndNormalizeImage($imgNDArray, $payload->getConfigInputShape());


    $predictions = $model->predict($normalizedImages);
    $predictionsArray = $predictions->toArray();

    var_dump($predictionsArray);
    $max = array_keys($predictionsArray, max($predictionsArray));

    if ($testMode) {
        exit(0);
    }

    match (reset($max)) {
        1 => $motor->forward(),
        2 => $motor->left(),
        3 => $motor->right(),
        default => null,
    };
}