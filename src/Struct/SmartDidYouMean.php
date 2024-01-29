<?php

namespace Findologic\Struct;

use Plenty\Plugin\Translation\Translator;


class SmartDidYouMean
{
    protected const DID_YOU_MEAN = 'did-you-mean';
    protected const IMPROVED = 'improved';
    protected const CORRECTED = 'corrected';

    public ?string $link;
    public ?string $type;

    private Translator $translator;

    public function __construct(
        public ?string $originalQuery,
        public ?string $effectiveQuery,
        public ?string $correctedQuery,
        public ?string $didYouMeanQuery,
        public ?string $improvedQuery,
        ?string $controllerPath
    ) {
        $this->translator = pluginApp(Translator::class);
        $this->originalQuery = htmlentities($originalQuery ?? '');
        $this->effectiveQuery = htmlentities($effectiveQuery ?? '');
        $this->correctedQuery = $this->translator->trans(
            'Findologic::Template.correctedQuery',
            [
                'originalQuery' => $originalQuery,
                'alternativeQuery' => htmlentities($correctedQuery ?? '')
            ]
        );
        $this->didYouMeanQuery = $this->translator->trans(
            'Findologic::Template.didYouMeanQuery',
            [
                'originalQuery' => $originalQuery,
                'alternativeQuery' => htmlentities($didYouMeanQuery ?? '')
            ]
        );
        $this->improvedQuery = $this->translator->trans(
            'Findologic::Template.improvedQuery',
            [
                'originalQuery' => $originalQuery,
                'alternativeQuery' => htmlentities($improvedQuery ?? '')
            ]
        );

        $this->type = $this->defineType();
        $this->link = $this->createLink($controllerPath);
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

    private function createLink(?string $controllerPath): ?string
    {
        return match ($this->type) {
            self::DID_YOU_MEAN => sprintf(
                '%s?search=%s&forceOriginalQuery=1',
                $controllerPath,
                $this->didYouMeanQuery
            ),
            self::IMPROVED => sprintf(
                '%s?search=%s&forceOriginalQuery=1',
                $controllerPath,
                $this->improvedQuery
            ),
            default => null,
        };
    }

    public function getType()
    {
        return $this->type;
    }
}