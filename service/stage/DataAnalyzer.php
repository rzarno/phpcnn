<?php

namespace service\stage;

use League\Pipeline\StageInterface;
use service\model\Payload;

class DataAnalyzer implements StageInterface
{

    /**
     * @param Payload $payload
     * @return Payload
     */
    public function __invoke($payload)
    {
        return $payload;
    }
}