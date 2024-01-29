<?php

namespace Findologic\Struct\QueryInfoMessage;

use Plenty\Plugin\Translation\Translator;

class SearchTermQueryInfoMessage extends QueryInfoMessage
{
    public string $query;
    private Translator $translator;

    public function __construct(
        string $query,
        int $count
    ) {
        $this->translator = pluginApp(Translator::class);
        $this->query = $this->translator->trans(
            'Findologic::Template.queryInfoMessageQuery',
            [
                'query' => $query,
                'hits' => $count
            ]
        );;
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
