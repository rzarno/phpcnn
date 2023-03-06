<?php
require __DIR__.'/../vendor/autoload.php';

use League\Pipeline\FingersCrossedProcessor;
use League\Pipeline\Pipeline;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Plot\Plot;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use Interop\Polite\Math\Matrix\NDArray;
use service\ImagePreprocesor;
use service\ImageTransform;
use service\LabelEncoder;
use service\model\Payload;
use service\ModelCNNArchitectureFactory;
use service\DataProvider;
use service\ModelTraining;
use service\ModelEvaluator;
use service\TrainTestSplit;

$matrixOperator = new MatrixOperator();
$plot = new Plot();
$dataProvider = new DataProvider(new ImageTransform(), new LabelEncoder());
$neuralNetworks = new NeuralNetworks($matrixOperator);
$cnnFactory = new ModelCNNArchitectureFactory($neuralNetworks);
$modelTrain = new ModelTraining($plot, $matrixOperator);
$resultsEvaluator = new ModelEvaluator($plot);
$trainTestSplit = new TrainTestSplit();
$imagePreprocessor = new ImagePreprocesor($matrixOperator);

$payload = new Payload(
    $configModelVersion = '1.0',
    $configEpochs = 10,
    $configBatchSize = 64,
    $configImgWidth = 102,
    $configImgHeight = 40,
    $configNumImgLayers = 3,
    $configModelFilePath = __DIR__."/../model/image-classification-with-cnn-{$configModelVersion}.model",
    $configClassNames = [1, 2, 3]
);

$pipeline = (new Pipeline(new FingersCrossedProcessor()))
    ->pipe($dataProvider)
    ->pipe($trainTestSplit);




echo "formating train image ...\n";
$trainImg = $imagePreprocessor->flattenAndNormalizeImage($trainImg, $inputShape);
$trainLabel = $matrixOperator->la()->astype($trainLabel,NDArray::int32);
echo "formating test image ...\n";
$testImg  = $imagePreprocessor->flattenAndNormalizeImage($testImg, $inputShape);
$testLabel = $matrixOperator->la()->astype($testLabel,NDArray::int32);

if(file_exists($modelFilePath)) {
    echo "loading model ...\n";
    $model = $neuralNetworks->models()->loadModel($modelFilePath);
    $model->summary();
} else {
    echo "creating model ...\n";
//    $model = $cnnFactory->createRinbowCNN($nn, $inputShape);
    $model = $cnnFactory->createNvidiaCNNDave2($inputShape);
    echo "training model ...\n";
    $modelTrain->trainModel($neuralNetworks, $model, $trainImg, $trainLabel, $testImg, $testLabel, $batchSize, $epochs, $modelFilePath);
}

$images = $testImg[[200,400]];
$labels = $testLabel[[200,400]];
$predicts = $model->predict($images);

$resultsEvaluator->evaluate($predicts, $labels);

