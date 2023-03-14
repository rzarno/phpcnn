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
use service\stage\DriveImageDataProvider;
use service\stage\ImagePreprocesor;
use service\stage\ModelCNNArchitectureFactory;
use service\stage\ModelEvaluator;
use service\stage\ModelExport;
use service\stage\ModelTraining;
use service\stage\TrainTestSplit;

$matrixOperator = new MatrixOperator();
$plot = new Plot();
$dataProvider = new DriveImageDataProvider();
$dataAnalyzer = new DataAnalyzer($plot, $matrixOperator);
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

$pipeline = (new Pipeline(new FingersCrossedProcessor()))
    ->pipe($dataProvider)
    ->pipe($dataImputer)
    ->pipe($dataAnalyzer)
    ->pipe($trainTestSplit)
    ->pipe($imagePreprocessor)
    ->pipe($cnnModelFactory)
    ->pipe($modelTrain)
    ->pipe($modelExport)
    ->pipe($resultsEvaluator);

$pipeline->process($payload);

