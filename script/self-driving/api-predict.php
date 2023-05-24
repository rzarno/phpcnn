<?php

require __DIR__ . '/../../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Matrix\NDArrayPhp;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use service\ImageTransform;
use service\LabelEncoder;
use service\model\Payload;
use service\stage\DataImputer;
use service\stage\DriveImageDataProvider;
use service\stage\ImagePreprocesor;

$matrixOperator = new MatrixOperator();
$dataProvider = new DriveImageDataProvider();
$dataImputer = new DataImputer(new ImageTransform(), new LabelEncoder());
$neuralNetworks = new NeuralNetworks($matrixOperator);
$imagePreprocessor = new ImagePreprocesor($matrixOperator);


$payload = new Payload(
    $configModelVersion = '1.0',
    $configEpochs = 20,
    $configBatchSize = 64,
    $configImgWidth = 102,
    $configImgHeight = 40,
    $cropFromTop = 40,
    $imputeIterations = 1,
    $configNumImgLayers = 3,
    $configModelFilePath = __DIR__."/../../model/image-classification-with-cnn-{$configModelVersion}.model",
    $configClassNames = [1, 2, 3, 4],
    $configUseExistingModel = false
);

$json = file_get_contents('php://input');
$decoded = json_decode($json, true);
$path = __DIR__ . '/sample.jpg';
file_put_contents($path, base64_decode($decoded['file']));

$model = $neuralNetworks->models()->loadModel($payload->getConfigModelFilePath());
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
$predictionsArray = $predictionsArray[0];
$max = array_keys($predictionsArray, max($predictionsArray));

echo reset($max);
