<?php
namespace service;

use Interop\Polite\Math\Matrix\NDArray;
use Rindow\Math\Plot\Plot;

class ModelEvaluator {

    public function __construct(
        private Plot $plt
    ){
    }

    public function showResulPlot()
    {
        $plt = new Plot(null,$mo);
        $plt->setConfig([
            'frame.xTickLength'=>0,'title.position'=>'down','title.margin'=>0,]);
        [$fig,$axes] = $this->plt->subplots(4,4);
        foreach ($predicts as $i => $predict) {
            $axes[$i*2]->imshow($images[$i]->reshape($inputShape),
                null,null,null,$origin='upper');
            $axes[$i*2]->setFrame(false);
            $label = $labels[$i];
            $axes[$i*2]->setTitle($classNames[$label]."($label)");
            $axes[$i*2+1]->bar($mo->arange(10),$predict);
        }

        $this->plt->show();
    }

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