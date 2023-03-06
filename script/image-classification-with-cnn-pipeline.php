<?php
require __DIR__.'/../vendor/autoload.php';

use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Plot\Plot;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use Interop\Polite\Math\Matrix\NDArray;
use service\ImagePreprocesor;
use service\ImageTransform;
use service\LabelEncoder;
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

$modelVersion = 1.0;
$epochs = 10;
$batch_size = 64;
$version = '1.0';
$imgWidth = 102;
$imgHeight = 40;
$numLayers = 3;
$modelFilePath = __DIR__."/../model/image-classification-with-cnn-{$version}.model";

[$sequenceImg, $sequenceLabel] = $dataProvider->importData();

[$trainImg, $testImg, $trainLabel, $testLabel] = $trainTestSplit->trainTestSplit($sequenceImg, $sequenceLabel, $imgWidth, $imgHeight, $numLayers);
$inputShape = [$imgWidth, $imgHeight, $numLayers];
$classNames = [1, 2, 3];

echo "train=[".implode(',',$trainImg->shape())."]\n";
echo "test=[".implode(',',$testImg->shape())."]\n";
echo "batch_size={$batch_size}\n";

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
    $modelTrain->trainModel($neuralNetworks, $model, $trainImg, $trainLabel, $testImg, $testLabel, $batch_size, $epochs, $modelFilePath);
}

$images = $testImg[[200,400]];
$labels = $testLabel[[200,400]];
$predicts = $model->predict($images);

$resultsEvaluator->evaluate($predicts, $labels);

