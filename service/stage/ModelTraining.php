<?php

namespace service\stage;

use Interop\Polite\Math\Matrix\NDArray;
use League\Pipeline\StageInterface;
use Rindow\Math\Matrix\MatrixOperator;
use Rindow\Math\Plot\Plot;
use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use Rindow\NeuralNetworks\Model\Sequential;
use service\model\Payload;

class ModelTraining implements StageInterface
{
    public function __construct(
        private readonly Plot $plt,
        private readonly MatrixOperator $matrixOperator,
        private readonly NeuralNetworks $neuralNetworks
    ) {}

    function trainModel(
        Sequential $model,
        NDArray $train_img,
        NDArray $train_label,
        NDArray $test_img,
        NDArray $test_label,
        int $batch_size,
        int $epochs,
        string $modelFilePath
    ) {
        $train_dataset = $this->neuralNetworks->data->ImageDataGenerator($train_img,
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

        $this->plt->plot($this->matrixOperator->array($history['accuracy']),null,null,'accuracy');
        $this->plt->plot($this->matrixOperator->array($history['val_accuracy']),null,null,'val_accuracy');
        $this->plt->plot($this->matrixOperator->array($history['loss']),null,null,'loss');
        $this->plt->plot($this->matrixOperator->array($history['val_loss']),null,null,'val_loss');
        $this->plt->legend();
        $this->plt->title('Lane driving action classification');
    }

    /**
     * @param Payload $payload
     * @return Payload
     */
    public function __invoke($payload)
    {
        echo "training model ...\n";
        $this->trainModel(
            $payload->getModel(),
            $payload->getNormalizedTrainImg(),
            $payload->getNormalizedTrainLabel(),
            $payload->getNormalizedTestImg(),
            $payload->getNormalizedTestLabel(),
            $payload->getConfigBatchSize(),
            $payload->getConfigNumEpochs(),
            $payload->getConfigModelFilePath()
        );

        return $payload;
    }
}