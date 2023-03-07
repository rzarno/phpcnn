<?php

namespace service\stage;

use Interop\Polite\Math\Matrix\NDArray;
use League\Pipeline\StageInterface;
use Rindow\Math\Matrix\NDArrayPhp;
use service\model\Payload;

class TrainTestSplit implements StageInterface
{
    public function trainTestSplit(array $sequenceImg, array $sequenceLabel, int $imgWidth, int $imgHeight, int $numLayers): array
    {
        $trainImg = array_slice($sequenceImg, 0, 4500);
        $testImg = array_slice($sequenceImg, 4500);

        $trainLabel = array_slice($sequenceLabel, 0, 4500);
        $testLabel = array_slice($sequenceLabel, 4500);

        $trainImgNDArray = new NDArrayPhp($trainImg, NDArray::int16, [4500, $numLayers, $imgWidth, $imgHeight]);
        $trainLabelNDArray = new NDArrayPhp($trainLabel, NDArray::int8, [4500]);
        $testImgNDArray = new NDArrayPhp($testImg, NDArray::int16, [1004, $numLayers, $imgWidth, $imgHeight]);
        $testLabelNDArray = new NDArrayPhp($testLabel, NDArray::int8, [1004]);
        return [$trainImgNDArray, $testImgNDArray, $trainLabelNDArray, $testLabelNDArray];
    }

    /**
     * @param Payload $payload
     * @return Payload
     */
    public function __invoke($payload)
    {
        echo "split to train and test set\n";
        [$trainImg, $testImg, $trainLabel, $testLabel] = $this->trainTestSplit(
            $payload->getSequenceImg(),
            $payload->getSequenceLabel(),
            $payload->getConfigImgWidth(),
            $payload->getConfigImgHeight(),
            $payload->getConfigNumImgLayers()
        );

        $payload->setTrainImg($trainImg)
            ->setTrainLabel($trainLabel)
            ->setTestImg($testImg)
            ->setTestLabel($testLabel);

        echo "train=[". implode(',', $trainImg->shape()) . "]\n";
        echo "test=[". implode(',', $testImg->shape()) . "]\n";
        echo "batch_size={" . $payload->getConfigBatchSize() . "}\n";

        return $payload;
    }
}