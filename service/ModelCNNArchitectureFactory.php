<?php

namespace service;

use Rindow\NeuralNetworks\Builder\NeuralNetworks;
use Rindow\NeuralNetworks\Model\Sequential;

class ModelCNNArchitectureFactory
{
    function rinbowCNN(NeuralNetworks $nn, $inputShape): Sequential
    {
        $model = $nn->models()->Sequential([
            $nn->layers()->Conv2D(
                $filters=64,
                $kernel_size=5,
                strides:[2,2],
                input_shape:$inputShape,
                kernel_initializer:'he_normal'),
            $nn->layers()->BatchNormalization(),
            $nn->layers()->Activation('relu'),
            $nn->layers()->Conv2D(
                $filters=64,
                $kernel_size=5,
                strides:[2,2],
                kernel_initializer:'he_normal'),
            $nn->layers()->MaxPooling2D(),
            $nn->layers()->Conv2D(
                $filters=128,
                $kernel_size=5,
                strides:[2,2],
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

    function createNvidiaCNNDave2(NeuralNetworks $nn, $inputShape): Sequential
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
}