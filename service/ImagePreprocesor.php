<?php

namespace service;

use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Matrix\MatrixOperator;

class ImagePreprocesor
{
    public function __construct(
      private MatrixOperator $matrixOperator
    ) {}

    function flattenAndNormalizeImage($train_img, $inputShape): NDArray
    {
        $dataSize = $train_img->shape()[0];
        $train_img = $train_img->reshape(array_merge([$dataSize],$inputShape));
        return $this->matrixOperator->scale(1.0/255.0, $this->matrixOperator->astype($train_img,NDArray::float32));
    }
}