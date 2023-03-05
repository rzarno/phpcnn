<?php
require __DIR__.'/../vendor/autoload.php';

use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Matrix\NDArrayPhp;
use Rindow\Math\Plot\Plot;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use Interop\Polite\Math\Matrix\NDArray;
use service\ImageTransform;
use service\LabelEncoder;
use service\ModelCNNArchitectureFactory;
use service\DataProvider;
use service\ModelTraining;

$modelVersion = 1.0;

$dataProvider = new DataProvider(new ImageTransform(), new LabelEncoder());
$cnnFactory = new ModelCNNArchitectureFactory();
$modelTrain = new ModelTraining();
$resultsEvaluator = new ResultsEvaluator();

$mo = new MatrixOperator();
$nn = new NeuralNetworks($mo);
$plt = new Plot(null,$mo);

$epochs = 10;
$batch_size = 64;
$version = '1.0';
[$sequenceImg, $sequenceLabel] = $dataProvider->importData();

$trainImg = array_slice($sequenceImg, 0, 4500);
$testImg = array_slice($sequenceImg, 4500);

$trainLabel = array_slice($sequenceLabel, 0, 4500);
$testLabel = array_slice($sequenceLabel, 4500);

unset($sequenceLabel);
unset($sequenceImg);

$train_img = new NDArrayPhp($trainImg, NDArray::int16, [4500,3,102,40]);
$train_label = new NDArrayPhp($trainLabel, NDArray::int8, [4500]);
$test_img = new NDArrayPhp($testImg, NDArray::int16, [1004,3,102,40]);
$test_label = new NDArrayPhp($testLabel, NDArray::int8, [1004]);
$inputShape = [102,40,3];
$class_names = [1, 2, 3];

echo "train=[".implode(',',$train_img->shape())."]\n";
echo "test=[".implode(',',$test_img->shape())."]\n";
echo "batch_size={$batch_size}\n";

// flatten image and normalize
function formatingImage($mo,$train_img,$inputShape) {
    $dataSize = $train_img->shape()[0];
    $train_img = $train_img->reshape(array_merge([$dataSize],$inputShape));
    return $mo->scale(1.0/255.0,$mo->astype($train_img,NDArray::float32));
}

echo "formating train image ...\n";
$train_img = formatingImage($mo,$train_img,$inputShape);
$train_label = $mo->la()->astype($train_label,NDArray::int32);
echo "formating test image ...\n";
$test_img  = formatingImage($mo,$test_img,$inputShape);
$test_label = $mo->la()->astype($test_label,NDArray::int32);

$modelFilePath = __DIR__."/image-classification-with-cnn-{$version}.model";

if(file_exists($modelFilePath)) {
    echo "loading model ...\n";
    $model = $nn->models()->loadModel($modelFilePath);
    $model->summary();
} else {
    echo "creating model ...\n";
//    $model = $cnnFactory->createRinbowCNN($nn, $inputShape);
    $model = $cnnFactory->createNvidiaCNNDave2($nn, $inputShape);
    echo "training model ...\n";
    $modelTrain->trainModel($nn, $mo, $model, $plt, $train_img, $train_label, $test_img, $test_label, $batch_size, $epochs, $modelFilePath);
}

$images = $test_img[[200,400]];
$labels = $test_label[[200,400]];
$predicts = $model->predict($images);

$resultsEvaluator->evaluate($predicts, $labels);

//if($inputShape[2]==1) {
//    array_pop($inputShape);
//}
$plt->setConfig([
    'frame.xTickLength'=>0,'title.position'=>'down','title.margin'=>0,]);
[$fig,$axes] = $plt->subplots(4,4);
foreach ($predicts as $i => $predict) {
    $axes[$i*2]->imshow($images[$i]->reshape($inputShape),
        null,null,null,$origin='upper');
    $axes[$i*2]->setFrame(false);
    $label = $labels[$i];
    $axes[$i*2]->setTitle($class_names[$label]."($label)");
    $axes[$i*2+1]->bar($mo->arange(10),$predict);
}

$plt->show();
