<?php
require __DIR__.'/../vendor/autoload.php';

use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Matrix\NDArrayPhp;
use Rindow\Math\Plot\Plot;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use Interop\Polite\Math\Matrix\NDArray;
use service\CNNArchitectureFactory;
use service\DataProvider;
use service\ModelTraining;

$dataProvider = new DataProvider();
$cnnFactory = new CNNArchitectureFactory();
$modelTrain = new ModelTraining();

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
$max = [];
foreach ($predicts as $single) {
    $max[] = array_keys($single->toArray(), max($single->toArray()))[0];
}

$count = count($max);
$count1 = 0;
$count2 = 0;
$count3 = 0;
$result = 0;
$result1 = 0;
$result2 = 0;
$result3 = 0;
$resultDetails = [];
$labelsArr = $labels->toArray();
foreach ($max as $key => $value) {
    $resultDetails[] = ['real'=> $labelsArr[$key], 'pred' => $value];
    switch($labelsArr[$key]) {
        case 1:
            $count1++;
            break;
        case 2:
            $count2++;
            break;
        case 3:
            $count3++;
            break;
    }
    if ($value === $labelsArr[$key]) {
        $result++;
        switch($value) {
            case 1:
                $result1++;
                break;
            case 2:
                $result2++;
                break;
            case 3:
                $result3++;
                break;
        }
    }
}

var_dump('correct predictions: ' . $result . ', ' . ($result/$count));
var_dump('correct predictions 1: ' . $result1 . '/' . $count1 . ', ' . ($result1/$count1));
var_dump('correct predictions 2: ' . $result2 . '/' . $count2 . ', ' . ($result2/$count2));
var_dump('correct predictions 3: ' . $result3 . '/' . $count3 . ', ' . ($result3/$count3));
var_dump($resultDetails);

if($inputShape[2]==1) {
    array_pop($inputShape);
}
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
