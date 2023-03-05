<?php

namespace service;

use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;

class ImagePreprocesor
{
    public function __construct(
      private MatrixOperator $matrixOperator
    ) {}

    function flattenAndNormalizeImage($trainImg, $inputShape): NDArray
    {
        $dataSize = $trainImg->shape()[0];
        $trainImg = $trainImg->reshape(array_merge([$dataSize],$inputShape));
        return $this->matrixOperator->scale(1.0/255.0, $this->matrixOperator->astype($trainImg,NDArray::float32));
    }
}