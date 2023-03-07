# Training Convolutional Neural Network in PHP

This project is about training CNN model that will lead self-driving vehicle to choose proper action (go forward, turn left, turn right) based on image from front camera.
Car has to follow track inside created lane.

<img src="./gallery/raspberry_pi_car.jpg" width="430"/>
<img src="./gallery/self_driving.gif" width="430"/>

## Project base components

CNN model is based on Nvidia "DAVE 2" proposed architecture

https://arxiv.org/pdf/1604.07316v1.pdf

model implementation and training was handled thanks to Rindow Neural Networks

https://github.com/rindow/rindow-neuralnetworks

main program is based on chain of responsibility design pattern implemented using league/pipeline and containing stages:
1. Import data
2. Analyze dataset
3. Impute more data based on imported images
4. Split data to training and test set
5. Preprocess images - scale and flatten
6. Build convolutional neural network model from specified layers
7. Train model
8. Export model
9. Evaluate model


## Setup

To train and test model run:

`composer install`

`php script/image-classification-with-cnn-pipeline.php`