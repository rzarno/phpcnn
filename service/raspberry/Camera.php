<?php

namespace service\raspberry;

use Cvuorinen\Raspicam\Raspistill;

class Camera
{
    private readonly Raspistill $camera;
    public function __construct()
    {
        $this->camera = new Raspistill();
    }

    public function takePhoto(string $path)
    {
        $this->camera->takePicture($path);
    }
}
