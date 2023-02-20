<?php
$sequenceImg = [];
$sequenceLabel = [];
$parentPath = '../image/sequence';
foreach (new DirectoryIterator($parentPath) as $fileInfo) {
    if (! $fileInfo->isDir()) {
        continue;
    }

    foreach (new DirectoryIterator($parentPath . '/' . $fileInfo->getFilename()) as $file) {
        if ($file->isDot()) {
            continue;
        }
        if (strpos($file->getFilename(), 'sequence') !== false) {
            if (! $currentSequence = json_decode(file_get_contents($file->getPathname()), true)) {
                continue;
            }
            $currentProcessedImg = [];
            $currentProcessedLabel = [];
            foreach ($currentSequence['sequence'] as $step) {
                $action = null;
                switch ($step['action']) {
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
                if (! $action) {
                    continue;
                }
                $photoPath = str_replace('./sequences', '../image/sequence', $step['photo']);
                /* Create new object */
                $im = new \Imagick($photoPath);
                /* Export the image pixels */
                $im->resizeImage(30, 30, imagick::FILTER_GAUSSIAN, 1.5);
                $pixels = $im->exportImagePixels(0, 0, $im->getImageWidth(),  $im->getImageHeight(), "I", \Imagick::PIXEL_CHAR);

                /* Output */
//                var_dump($pixels);
                $currentProcessedImg[] = $pixels;
                $currentProcessedLabel[] = $action;
            }
            $sequenceImg = array_merge($sequenceImg, $currentProcessedImg);
            $sequenceLabel = array_merge($sequenceLabel, $currentProcessedLabel);
        }
    }
}

$img = 1;