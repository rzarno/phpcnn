<?php
require __DIR__.'/../vendor/autoload.php';

use League\Pipeline\FingersCrossedProcessor;
use League\Pipeline\Pipeline;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Plot\Plot;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use service\ImageTransform;
use service\model\Payload;
use service\stage\CaptchaCharEncoder;
use service\stage\CaptchaImageCharExtractor;
use service\stage\CaptchaImageDataProvider;
use service\stage\DataAnalyzer;
use service\stage\ImagePreprocesor;
use service\stage\ModelCNNArchitectureFactory;
use service\stage\ModelEvaluator;
use service\stage\ModelExport;
use service\stage\ModelTraining;
use service\stage\TrainTestSplit;

$matrixOperator = new MatrixOperator();
$plot = new Plot();
$dataProvider = new CaptchaImageDataProvider();
$dataAnalyzer = new DataAnalyzer();
$charImageExtractor = new CaptchaImageCharExtractor(new ImageTransform(), new CaptchaCharEncoder());
$neuralNetworks = new NeuralNetworks($matrixOperator);
$cnnModelFactory = new ModelCNNArchitectureFactory($neuralNetworks);
$modelTrain = new ModelTraining($plot, $matrixOperator, $neuralNetworks);
$resultsEvaluator = new ModelEvaluator($plot, $matrixOperator);
$trainTestSplit = new TrainTestSplit();
$imagePreprocessor = new ImagePreprocesor($matrixOperator);
$modelExport = new ModelExport();

$payload = new Payload(
    $configModelVersion = '1.0',
    $configEpochs = 20,
    $configBatchSize = 64,
    $configImgWidth = 40,
    $configImgHeight = 50,
    $cropFromTop = 0,
    $imputeIterations = 0,
    $configNumImgLayers = 3,
    $configModelFilePath = __DIR__."/../model/char-classification-with-cnn-{$configModelVersion}.model",
    $configClassNames = ['6', '2', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'W', 'V', 'X', 'Y', 'Z'],
    $configUseExistingModel = false
);

$pipeline = (new Pipeline(new FingersCrossedProcessor()))
    ->pipe($dataProvider)
    ->pipe($dataAnalyzer)
    ->pipe($charImageExtractor)
    ->pipe($trainTestSplit)
    ->pipe($imagePreprocessor)
    ->pipe($cnnModelFactory)
    ->pipe($modelTrain)
    ->pipe($modelExport)
    ->pipe($resultsEvaluator);

$pipeline->process($payload);

