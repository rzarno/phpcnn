<?php

namespace service\raspberry;

use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\PinInterface;

class Motor
{
    private const ENA = 17;
    private const IN1 = 27;
    private const IN2 = 22;

    private const ENB = 13;
    private const IN3 = 6;
    private const IN4 = 5;

    private const DEFAULT_SLEEPSEC = 1;

    private GPIO $gpio;

    public function __construct() {
        $this->gpio = new GPIO();
    }

    public function forward()
    {
        $this->gpio->getOutputPin(self::ENA)->setValue(PinInterface::VALUE_HIGH);
        $this->gpio->getOutputPin(self::ENB)->setValue(PinInterface::VALUE_HIGH);
        $this->gpio->getOutputPin(self::IN1)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN2)->setValue(PinInterface::VALUE_HIGH);
        $this->gpio->getOutputPin(self::IN3)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN4)->setValue(PinInterface::VALUE_HIGH);
        sleep(self::DEFAULT_SLEEPSEC);
        $this->stop();
    }

    public function left()
    {
        $this->gpio->getOutputPin(self::ENA)->setValue(PinInterface::VALUE_HIGH);
        $this->gpio->getOutputPin(self::IN1)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN2)->setValue(PinInterface::VALUE_HIGH);
        $this->gpio->getOutputPin(self::ENB)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN3)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN4)->setValue(PinInterface::VALUE_LOW);
        sleep(self::DEFAULT_SLEEPSEC);
        $this->stop();
    }

    public function right()
    {
        $this->gpio->getOutputPin(self::ENB)->setValue(PinInterface::VALUE_HIGH);
        $this->gpio->getOutputPin(self::IN3)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN4)->setValue(PinInterface::VALUE_HIGH);
        $this->gpio->getOutputPin(self::ENA)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN1)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN2)->setValue(PinInterface::VALUE_LOW);
        sleep(self::DEFAULT_SLEEPSEC);
        $this->stop();
    }

    public function stop()
    {
        $this->gpio->getOutputPin(self::ENA)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN1)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN2)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::ENB)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN3)->setValue(PinInterface::VALUE_LOW);
        $this->gpio->getOutputPin(self::IN4)->setValue(PinInterface::VALUE_LOW);
    }
}