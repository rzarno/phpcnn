<?php

namespace service;

class LabelEncoder
{
    function encodeAction(string $actionBefore): ?int
    {
        return match ($actionBefore) {
            'forward' => 1,
            'left' => 2,
            'right' => 3,
            default => null
        };
    }
}