<?php

namespace service\stage;

use Imagick;
use League\Pipeline\StageInterface;
use service\ImageTransform;
use service\LabelEncoder;
use service\model\Payload;

class DataImputer implements StageInterface
{
    public function __construct(
        private readonly ImageTransform $imageTransform,
        private readonly LabelEncoder $labelEncoder
    ) {}

    public function imputeData(array $images)
    {
        echo "impute data\n";
        $sequenceImg = [];
        $sequenceLabel = [];
        foreach ($images as $photoPath => $action) {
            $im = new Imagick($photoPath);
            /* Export the image pixels */
            $im->resizeImage(102, 80, Imagick::FILTER_GAUSSIAN, 1);
            $im->cropImage(102, 40, 0, 40);
            $im->setColorspace(Imagick::COLORSPACE_YUV);

            $currentProcessedImg = [];
            $currentProcessedLabel = [];
            $pixels = $this->imageTransform->exportRGBArray($im);
            $currentProcessedImg[] = $pixels;
            $currentProcessedLabel[] = $this->labelEncoder->encodeAction($action);
            for ($i = 0; $i < 10; $i++) {
                $imNew = $this->imageTransform->modifyImageRandomly($im);
                $pixels = $this->imageTransform->exportRGBArray($imNew);
                $currentProcessedImg[] = $pixels;
                $currentProcessedLabel[] = $this->labelEncoder->encodeAction($action);;
//                    $imNew->writeImage("sample$i$j.png");
            }
            $sequenceImg = array_merge($sequenceImg, $currentProcessedImg);
            $sequenceLabel = array_merge($sequenceLabel, $currentProcessedLabel);
        }

        $result = [];
        foreach ($sequenceImg as $key => $val) {
            $result[$key] = [$val, $sequenceLabel[$key]];
        }

        shuffle($result);

        $sequenceLabel = [];
        $sequenceImg = [];
        foreach ($result as $key => $val) {
            $sequenceImg[] = $val[0];
            $sequenceLabel[] = $val[1];
        }
        return [$sequenceImg, $sequenceLabel];
    }

    /**
     * @param Payload $payload
     * @return Payload
     */
    public function __invoke($payload)
    {
        [$sequenceImg, $sequenceLabel] = $this->imputeData($payload->getImportedData());
        $payload->setImportedData(null)
            ->setSequenceImg($sequenceImg)
            ->setSequenceLabel($sequenceLabel);
        return $payload;
    }
}