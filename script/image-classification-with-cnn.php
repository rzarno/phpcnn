<?php
require __DIR__.'/../vendor/autoload.php';

use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Matrix\NDArrayPhp;
use Rindow\Math\Plot\Plot;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use Interop\Polite\Math\Matrix\NDArray;
use Rindow\NeuralNetworks\Model\Sequential;

$mo = new MatrixOperator();
$nn = new NeuralNetworks($mo);
$plt = new Plot(null,$mo);

$epochs = 10;
$batch_size = 64;
$version = '1.0';
[$sequenceImg, $sequenceLabel] = importData();

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
//    $model = rinbowCNN($nn, $inputShape);
    $model = nvidiaCNNDave2($nn, $inputShape);
    echo "training model ...\n";
    trainModel($nn, $mo, $model, $plt, $train_img, $train_label, $test_img, $test_label, $batch_size, $epochs, $modelFilePath);
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


function importData()
{
    $sequenceImg = [];
    $sequenceLabel = [];
    $parentPath = '../image/sequence';
    foreach (new DirectoryIterator($parentPath) as $j => $fileInfo) {
        if (!$fileInfo->isDir()) {
            continue;
        }

        foreach (new DirectoryIterator($parentPath . '/' . $fileInfo->getFilename()) as $file) {
            if ($file->isDot()) {
                continue;
            }
            if (strpos($file->getFilename(), 'sequence') !== false) {
                if (! is_file($file->getPathname())) {
                    continue;
                }
                if (!$currentSequence = json_decode(file_get_contents($file->getPathname()), true)) {
                    continue;
                }
                $currentProcessedImg = [];
                $currentProcessedLabel = [];
                foreach ($currentSequence['sequence'] as $step) {
                    $action = encodeAction($step['action']);
                    if (!$action) {
                        continue;
                    }
                    $photoPath = str_replace('./sequences', '../image/sequence', $step['photo']);
                    /* Create new object */
                    $im = new \Imagick($photoPath);
                    /* Export the image pixels */
                    $im->resizeImage(102, 80, imagick::FILTER_GAUSSIAN, 1);
                    $im->cropImage(102, 40, 0, 40);
                    $im->setColorspace(imagick::COLORSPACE_YUV);
                    if (count($currentProcessedImg) === 12) {
//                    $im->writeImage('sample.png');
                    }

                    $pixels = exportRGBArray($im);
                    $currentProcessedImg[] = $pixels;
                    $currentProcessedLabel[] = $action;
                    for ($i = 0; $i < 10; $i++) {
                        $imNew = modifyImageRandomly($im);
                        $pixels = exportRGBArray($imNew);
                        $currentProcessedImg[] = $pixels;
                        $currentProcessedLabel[] = $action;
//                    $imNew->writeImage("sample$i$j.png");
                    }
                }
                $sequenceImg = array_merge($sequenceImg, $currentProcessedImg);
                $sequenceLabel = array_merge($sequenceLabel, $currentProcessedLabel);
            }
        }
    }
    $result = [];
    foreach ($sequenceImg as $key => $val) {
        $result[$key] = [$val, $sequenceLabel[$key]];
    }

    shuffle($result);

    $sequenceLabel = [];
    $sequenceImg = [];
    foreach ($result as $key => $val) {
        $sequenceImg[] = $val[0];
        $sequenceLabel[] = $val[1];
    }
    return [$sequenceImg, $sequenceLabel];
}

function exportRGBArray(\Imagick $im)
{
    $pixels = [
        $im->exportImagePixels(0, 0, $im->getImageWidth(), $im->getImageHeight(), "R", \Imagick::PIXEL_CHAR),
        $im->exportImagePixels(0, 0, $im->getImageWidth(), $im->getImageHeight(), "G", \Imagick::PIXEL_CHAR),
        $im->exportImagePixels(0, 0, $im->getImageWidth(), $im->getImageHeight(), "B", \Imagick::PIXEL_CHAR),
    ];
    return $pixels;
}

function encodeAction(string $actionBefore)
{
    $action = null;
    switch ($actionBefore) {
        case 'forward':
            $action = 1;
            break;
        case 'left':
            $action = 2;
            break;
        case 'right':
            $action = 3;
            break;
    }
    return $action;
}

function modifyImageRandomly(Imagick $im) {
    $im = clone $im;
    if (rand(1,2) == 2) {
        $im->brightnessContrastImage(10, 10);
    } else {
        $im->brightnessContrastImage(5, 20);
    }
//    if (rand(1,2) == 2) {
//        $im->cropImage(97, 30, 5, 10);
//        $im->scaleImage(102, 30);
//    }
    if (rand(1,2) == 2) {
        $im->flopImage();
    }
    return $im;
}

function rinbowCNN(NeuralNetworks $nn, $inputShape): Sequential
{
    $model = $nn->models()->Sequential([
        $nn->layers()->Conv2D(
            $filters=64,
            $kernel_size=3,
            input_shape:$inputShape,
            kernel_initializer:'he_normal'),
        $nn->layers()->BatchNormalization(),
        $nn->layers()->Activation('relu'),
        $nn->layers()->Conv2D(
            $filters=64,
            $kernel_size=3,
            kernel_initializer:'he_normal'),
        $nn->layers()->MaxPooling2D(),
        $nn->layers()->Conv2D(
            $filters=128,
            $kernel_size=3,
            kernel_initializer:'he_normal'),
        $nn->layers()->BatchNormalization(),
        $nn->layers()->Activation('relu'),
        $nn->layers()->Conv2D(
            $filters=128,
            $kernel_size=3,
            kernel_initializer:'he_normal'),
        $nn->layers()->MaxPooling2D(),
        $nn->layers()->Conv2D(
            $filters=256,
            $kernel_size=3,
            kernel_initializer:'he_normal',
            activation:'relu'),
        $nn->layers()->GlobalAveragePooling2D(),
        $nn->layers()->Dense($units=512,
            kernel_initializer:'he_normal'),
        $nn->layers()->BatchNormalization(),
        $nn->layers()->Activation('relu'),
        $nn->layers()->Dense($units=10,
            activation:'softmax'),
    ]);

    $model->compile(
        loss:'sparse_categorical_crossentropy',
        optimizer:'adam',
    );
    $model->summary();
    return $model;
}

function nvidiaCNNDave2(NeuralNetworks $nn, $inputShape): Sequential
{
    $model = $nn->models()->Sequential([
        $nn->layers()->Conv2D(
            $filters=64,
            $kernel_size=5,
            strides:2,
            input_shape:$inputShape,
            kernel_initializer:'he_normal'),
        $nn->layers()->BatchNormalization(),
        $nn->layers()->Activation('relu'),
        $nn->layers()->Conv2D(
            $filters=64,
            $kernel_size=5,
            strides:2,
            kernel_initializer:'he_normal'),
        $nn->layers()->MaxPooling2D(),
        $nn->layers()->Conv2D(
            $filters=128,
            $kernel_size=5,
            strides:2,
            kernel_initializer:'he_normal'),
        $nn->layers()->BatchNormalization(),
        $nn->layers()->Activation('relu'),
        $nn->layers()->Conv2D(
            $filters=128,
            $kernel_size=3,
            kernel_initializer:'he_normal'),
        $nn->layers()->MaxPooling2D(),
        $nn->layers()->Conv2D(
            $filters=256,
            $kernel_size=3,
            kernel_initializer:'he_normal',
            activation:'relu'),
        $nn->layers()->GlobalAveragePooling2D(),

        $nn->layers()->Dense($units=512,
            kernel_initializer:'he_normal'),
        $nn->layers()->BatchNormalization(),
        $nn->layers()->Activation('relu'),
        $nn->layers()->Flatten(),
        $nn->layers()->Dropout(0.2),
        $nn->layers()->Dense($units=100, activation:'relu'),
        $nn->layers()->Dense($units=50, activation:'relu'),
        $nn->layers()->Dense($units=10,
            activation:'softmax'),
    ]);

    $model->compile(
        loss:'sparse_categorical_crossentropy',
        optimizer:'adam',
    );
    $model->summary();
    return $model;
}

function trainModel(
    NeuralNetworks $nn,
    MatrixOperator $mo,
    Sequential $model,
    Plot $plt,
   $train_img,
   $train_label,
   $test_img,
   $test_label,
   $batch_size,
   $epochs,
   $modelFilePath
) {
    $train_dataset = $nn->data->ImageDataGenerator($train_img,
        tests:$train_label,
        batch_size:$batch_size,
        shuffle:true,
        height_shift:2,
        width_shift:2,
        vertical_flip:true,
        horizontal_flip:true
    );
    $history = $model->fit($train_dataset,null,
        epochs:$epochs,
        validation_data:[$test_img,$test_label]);
    $model->save($modelFilePath,$portable=true);
    $plt->plot($mo->array($history['accuracy']),null,null,'accuracy');
    $plt->plot($mo->array($history['val_accuracy']),null,null,'val_accuracy');
    $plt->plot($mo->array($history['loss']),null,null,'loss');
    $plt->plot($mo->array($history['val_loss']),null,null,'val_loss');
    $plt->legend();
    $plt->title('Lane driving action classification');
}

