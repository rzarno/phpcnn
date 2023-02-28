<?php

namespace service;

use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Plot\Plot;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use Rindow\NeuralNetworks\Model\Sequential;

class ModelTraining
{
    function trainModel(
        NeuralNetworks $nn,
        MatrixOperator $mo,
        Sequential $model,
        Plot $plt,
        NDArray $train_img,
        NDArray $train_label,
        NDArray $test_img,
        NDArray $test_label,
        int $batch_size,
        int $epochs,
        string $modelFilePath
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
}