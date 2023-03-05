<?php

namespace service;

use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\NDArrayPhp;

class TrainTestSplit
{
    public function trainTestSplit(array $sequenceImg, array $sequenceLabel, int $imgWidth, int $imgHeight, int $numLayers): array
    {
        $trainImg = array_slice($sequenceImg, 0, 4500);
        $testImg = array_slice($sequenceImg, 4500);

        $trainLabel = array_slice($sequenceLabel, 0, 4500);
        $testLabel = array_slice($sequenceLabel, 4500);

        $trainImgNDArray = new NDArrayPhp($trainImg, NDArray::int16, [4500, $numLayers, $imgWidth, $imgHeight]);
        $trainLabelNDArray = new NDArrayPhp($trainLabel, NDArray::int8, [4500]);
        $testImgNDArray = new NDArrayPhp($testImg, NDArray::int16, [1004,$numLayers, $imgWidth, $imgHeight]);
        $testLabelNDArray = new NDArrayPhp($testLabel, NDArray::int8, [1004]);
        return [$trainImgNDArray, $trainLabelNDArray, $testImgNDArray, $testLabelNDArray];
    }
}