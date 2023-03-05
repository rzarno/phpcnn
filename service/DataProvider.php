<?php

namespace service;

use DirectoryIterator;
use Imagick;

class DataProvider
{
    private ImageTransform $imageTransform;
    private LabelEncoder $labelEncoder;
    public function __construct(
        ImageTransform $imageTransform,
        LabelEncoder $labelEncoder
    ) {
        $this->imageTransform = $imageTransform;
        $this->labelEncoder = $labelEncoder;
    }

    function importData()
    {
        $sequenceImg = [];
        $sequenceLabel = [];
        $parentPath = '../image/sequence';
        foreach (new DirectoryIterator($parentPath) as $fileInfo) {
            if (!$fileInfo->isDir()) {
                continue;
            }

            foreach (new DirectoryIterator($parentPath . '/' . $fileInfo->getFilename()) as $file) {
                if ($file->isDot()) {
                    continue;
                }
                if (strpos($file->getFilename(), 'sequence') !== false) {
                    if (!is_file($file->getPathname())) {
                        continue;
                    }
                    if (!$currentSequence = json_decode(file_get_contents($file->getPathname()), true)) {
                        continue;
                    }
                    $currentProcessedImg = [];
                    $currentProcessedLabel = [];
                    foreach ($currentSequence['sequence'] as $step) {
                        $action = $this->labelEncoder->encodeAction($step['action']);
                        if (!$action) {
                            continue;
                        }
                        $photoPath = str_replace('./sequences', '../image/sequence', $step['photo']);
                        /* Create new object */
                        $im = new Imagick($photoPath);
                        /* Export the image pixels */
                        $im->resizeImage(102, 80, Imagick::FILTER_GAUSSIAN, 1);
                        $im->cropImage(102, 40, 0, 40);
                        $im->setColorspace(Imagick::COLORSPACE_YUV);
                        if (count($currentProcessedImg) === 12) {
//                    $im->writeImage('sample.png');
                        }

                        $pixels = $this->imageTransform->exportRGBArray($im);
                        $currentProcessedImg[] = $pixels;
                        $currentProcessedLabel[] = $action;
                        for ($i = 0; $i < 10; $i++) {
                            $imNew = $this->imageTransform->modifyImageRandomly($im);
                            $pixels = $this->imageTransform->exportRGBArray($imNew);
                            $currentProcessedImg[] = $pixels;
                            $currentProcessedLabel[] = $action;
//                    $imNew->writeImage("sample$i$j.png");
                        }
                    }
                    $sequenceImg = array_merge($sequenceImg, $currentProcessedImg);
                    $sequenceLabel = array_merge($sequenceLabel, $currentProcessedLabel);
                }
            }
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
}