<?php

namespace service\model;

use Rindow\Math\Matrix\NDArrayPhp;

class Payload
{
    private array $sequenceImg;
    private array $sequenceLabel;
    private array $configInputShape;
    private NDArrayPhp $trainImg;
    private NDArrayPhp $testImg;
    private NDArrayPhp $trainLabel;
    private NDArrayPhp $testLabel;

    public function __construct(
        private readonly string $configModelVersion,
        private readonly int $configNumEpochs,
        private readonly int $configBatchSize,
        private readonly int $configImgWidth,
        private readonly int $configImgHeight,
        private readonly int $configNumImgLayers,
        private readonly string $configModelFilePath,
        private readonly array $configClassNames
    ) {
        $this->configInputShape = [$this->configImgWidth, $this->configImgHeight, $this->configNumImgLayers];
    }

    public function getConfigModelVersion(): string
    {
        return $this->configModelVersion;
    }

    public function getConfigNumEpochs(): int
    {
        return $this->configNumEpochs;
    }

    public function getConfigBatchSize(): int
    {
        return $this->configBatchSize;
    }

    public function getConfigImgWidth(): int
    {
        return $this->configImgWidth;
    }

    public function getConfigImgHeight(): int
    {
        return $this->configImgHeight;
    }

    public function getConfigNumImgLayers(): int
    {
        return $this->configNumImgLayers;
    }

    public function getConfigModelFilePath(): string
    {
        return $this->configModelFilePath;
    }

    public function getConfigInputShape(): array
    {
        return $this->configInputShape;
    }

    public function getConfigClassNames(): array
    {
        return $this->configClassNames;
    }

    public function getSequenceImg(): array
    {
        return $this->sequenceImg;
    }

    public function setSequenceImg(array $sequenceImg): self
    {
        $this->sequenceImg = $sequenceImg;
        return $this;
    }

    public function getSequenceLabel(): array
    {
        return $this->sequenceLabel;
    }

    public function setSequenceLabel(array $sequenceLabel): self
    {
        $this->sequenceLabel = $sequenceLabel;
        return $this;
    }

    /**
     * @return NDArrayPhp
     */
    public function getTrainImg(): NDArrayPhp
    {
        return $this->trainImg;
    }

    /**
     * @param NDArrayPhp $trainImg
     * @return Payload
     */
    public function setTrainImg(NDArrayPhp $trainImg): Payload
    {
        $this->trainImg = $trainImg;
        return $this;
    }

    /**
     * @return NDArrayPhp
     */
    public function getTestImg(): NDArrayPhp
    {
        return $this->testImg;
    }

    /**
     * @param NDArrayPhp $testImg
     * @return Payload
     */
    public function setTestImg(NDArrayPhp $testImg): Payload
    {
        $this->testImg = $testImg;
        return $this;
    }

    public function getTrainLabel(): NDArrayPhp
    {
        return $this->trainLabel;
    }

    public function setTrainLabel(NDArrayPhp $trainLabel): Payload
    {
        $this->trainLabel = $trainLabel;
        return $this;
    }

    public function getTestLabel(): NDArrayPhp
    {
        return $this->testLabel;
    }

    public function setTestLabel(NDArrayPhp $testLabel): Payload
    {
        $this->testLabel = $testLabel;
        return $this;
    }
}