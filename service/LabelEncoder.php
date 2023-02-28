<?php

namespace service;

class LabelEncoder
{
    function encodeAction(string $actionBefore)
    {
        $action = null;
        switch ($actionBefore) {
            case 'forward':
                $action = 1;
                break;
            case 'left':
                $action = 2;
                break;
            case 'right':
                $action = 3;
                break;
        }
        return $action;
    }
}