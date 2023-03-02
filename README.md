# Training Convolutional Neural Network in PHP

This project is about training CNN model that will lead self-driving vehicle to choose proper action (go forward, turn left, turn right) based on image from front camera.
Car has to follow track inside created lane.

<img src="./gallery/raspberry_pi_car.jpg" width="430"/>
<img src="./gallery/self_driving.gif" width="430"/>


CNN model is based on Nvidia "DAVE 2" proposed architecture

https://arxiv.org/pdf/1604.07316v1.pdf

model implementation and training was handled thanks to Rindow Neural Networks

https://github.com/rindow/rindow-neuralnetworks


To train and test model run:

`composer install`

`php image-classification-with-cnn.php`