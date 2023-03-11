<?php
require __DIR__.'/../vendor/autoload.php';

use League\Pipeline\FingersCrossedProcessor;
use League\Pipeline\Pipeline;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Plot\Plot;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use service\ImageTransform;
use service\LabelEncoder;
use service\model\Payload;
use service\stage\DataAnalyzer;
use service\stage\DataImputer;
use service\stage\DataProvider;
use service\stage\ImagePreprocesor;
use service\stage\ModelCNNArchitectureFactory;
use service\stage\ModelEvaluator;
use service\stage\ModelExport;
use service\stage\ModelTraining;
use service\stage\TrainTestSplit;

$matrixOperator = new MatrixOperator();
$plot = new Plot();
$dataProvider = new DataProvider();
$dataAnalyzer = new DataAnalyzer();
$dataImputer = new DataImputer(new ImageTransform(), new LabelEncoder());
$neuralNetworks = new NeuralNetworks($matrixOperator);
$cnnModelFactory = new ModelCNNArchitectureFactory($neuralNetworks);
$modelTrain = new ModelTraining($plot, $matrixOperator, $neuralNetworks);
$resultsEvaluator = new ModelEvaluator($plot, $matrixOperator);
$trainTestSplit = new TrainTestSplit();
$imagePreprocessor = new ImagePreprocesor($matrixOperator);
$modelExport = new ModelExport();

$payload = new Payload(
    $configModelVersion = '1.0',
    $configEpochs = 10,
    $configBatchSize = 64,
    $configImgWidth = 102,
    $configImgHeight = 40,
    $configNumImgLayers = 3,
    $configModelFilePath = __DIR__."/../model/image-classification-with-cnn-{$configModelVersion}.model",
    $configClassNames = [1, 2, 3],
    $configUseExistingModel = false
);

$pipeline = (new Pipeline(new FingersCrossedProcessor()))
    ->pipe($dataProvider)
    ->pipe($dataAnalyzer)
    ->pipe($dataImputer)
    ->pipe($trainTestSplit)
    ->pipe($imagePreprocessor)
    ->pipe($cnnModelFactory)
    ->pipe($modelTrain)
    ->pipe($modelExport)
    ->pipe($resultsEvaluator);

$pipeline->process($payload);

