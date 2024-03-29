<?php

namespace service\stage;

use DirectoryIterator;
use League\Pipeline\StageInterface;
use service\model\Payload;

class DriveImageDataProvider implements StageInterface
{
    public function importData(): array
    {
        echo "importing data\n";
        $parentPath = '../../image/drive-sequence';
        $images = [];
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
                    foreach ($currentSequence['sequence'] as $step) {
                        if (! $action = $step['action']) {
                            continue;
                        }
                        $photoPath = str_replace('./sequences', '../../image/drive-sequence', $step['photo']);
                        $images[$photoPath] = $action;
                    }
                }
            }
        }
        return $images;
    }

    /**
     * @param Payload $payload
     */
    public function __invoke($payload)
    {
        $images =  $this->importData();
        $payload->setImportedData($images);
        return $payload;
    }
}
