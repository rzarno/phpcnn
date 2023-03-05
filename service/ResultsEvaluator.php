<?php

use Interop\Polite\Math\Matrix\NDArray;

class ResultsEvaluator {
    public function evaluate(NDArray $predicts, NDArray $labels)
    {
        $max = [];
        foreach ($predicts as $single) {
            $max[] = array_keys($single->toArray(), max($single->toArray()))[0];
        }

        $count = count($max);
        $count1 = 0;
        $count2 = 0;
        $count3 = 0;
        $result = 0;
        $result1 = 0;
        $result2 = 0;
        $result3 = 0;
        $resultDetails = [];
        $labelsArr = $labels->toArray();
        foreach ($max as $key => $value) {
            $resultDetails[] = ['real'=> $labelsArr[$key], 'pred' => $value];
            switch($labelsArr[$key]) {
                case 1:
                    $count1++;
                    break;
                case 2:
                    $count2++;
                    break;
                case 3:
                    $count3++;
                    break;
            }
            if ($value === $labelsArr[$key]) {
                $result++;
                switch($value) {
                    case 1:
                        $result1++;
                        break;
                    case 2:
                        $result2++;
                        break;
                    case 3:
                        $result3++;
                        break;
                }
            }
        }

        var_dump('correct predictions: ' . $result . ', ' . ($result/$count));
        var_dump('correct predictions 1: ' . $result1 . '/' . $count1 . ', ' . ($result1/$count1));
        var_dump('correct predictions 2: ' . $result2 . '/' . $count2 . ', ' . ($result2/$count2));
        var_dump('correct predictions 3: ' . $result3 . '/' . $count3 . ', ' . ($result3/$count3));
        var_dump($resultDetails);
    }

}