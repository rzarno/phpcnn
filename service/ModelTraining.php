<?php

namespace service;

use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Plot\Plot;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use Rindow\NeuralNetworks\Model\Sequential;

class ModelTraining
{
    public function __construct(
        private Plot $plt,
        private MatrixOperator $matrixOperator
    ) {}

    function trainModel(
        NeuralNetworks $nn,
        Sequential $model,
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
        $this->plt->plot($this->matrixOperator->array($history['accuracy']),null,null,'accuracy');
        $this->plt->plot($this->matrixOperator->array($history['val_accuracy']),null,null,'val_accuracy');
        $this->plt->plot($this->matrixOperator->array($history['loss']),null,null,'loss');
        $this->plt->plot($this->matrixOperator->array($history['val_loss']),null,null,'val_loss');
        $this->plt->legend();
        $this->plt->title('Lane driving action classification');
    }
}