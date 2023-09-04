<?php

declare(strict_types=1);

namespace FINDOLOGIC\Components;


class SmartDidYouMean
{
    protected const DID_YOU_MEAN = 'did-you-mean';
    protected const IMPROVED = 'improved';
    protected const CORRECTED = 'corrected';

    private ?string $link;

    public function __construct(
        private ?string $originalQuery,
        private ?string $effectiveQuery,
        private ?string $correctedQuery,
        private ?string $didYouMeanQuery,
        private ?string $improvedQuery,
        ?string $controllerPath
    ) {
        $this->originalQuery = htmlentities($originalQuery ?? '');
        $this->effectiveQuery = htmlentities($effectiveQuery ?? '');
        $this->correctedQuery = htmlentities($correctedQuery ?? '');
        $this->didYouMeanQuery = htmlentities($didYouMeanQuery ?? '');
        $this->improvedQuery = htmlentities($improvedQuery ?? '');

        // $this->link = $this->createLink($controllerPath);
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getOriginalQuery(): string
    {
        return $this->originalQuery;
    }

    public function getEffectiveQuery(): string
    {
        return $this->effectiveQuery;
    }

    public function getCorrectedQuery(): string
    {
        return $this->correctedQuery;
    }

    public function getDidYouMeanQuery(): string
    {
        return $this->didYouMeanQuery;
    }

    public function getImprovedQuery(): string
    {
        return $this->improvedQuery;
    }

    public function getVars(): array
    {
        return [
            'link' => $this->link,
            'originalQuery' => $this->originalQuery,
            'effectiveQuery' => $this->effectiveQuery,
            'correctedQuery' => $this->correctedQuery,
            'improvedQuery' => $this->improvedQuery,
            'didYouMeanQuery' => $this->didYouMeanQuery,
        ];
    }

    private function defineType(): string
    {
        if ($this->didYouMeanQuery) {
            return self::DID_YOU_MEAN;
        } elseif ($this->improvedQuery) {
            return self::IMPROVED;
        } elseif ($this->correctedQuery) {
            return self::CORRECTED;
        }

        return '';
    }

    // private function createLink(?string $controllerPath): ?string
    // {
    //     return match ($this->type) {
    //         self::DID_YOU_MEAN => sprintf(
    //             '%s?search=%s&forceOriginalQuery=1',
    //             $controllerPath,
    //             $this->didYouMeanQuery
    //         ),
    //         self::IMPROVED => sprintf(
    //             '%s?search=%s&forceOriginalQuery=1',
    //             $controllerPath,
    //             $this->improvedQuery
    //         ),
    //         default => null,
    //     };
    // }
}