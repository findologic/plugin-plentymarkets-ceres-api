<?php

namespace Findologic\Helpers;

use Plenty\Plugin\Http\Request;

class Tags
{
    public function isTagPage(Request $request): bool
    {
        return (bool)preg_match('/\/.+_t\d+/', $request->getUri());
    }

    public function getTagIdFromUri(Request $request): int
    {
        preg_match('/\/.+_t\d+/', $request->getUri(), $matches);

        $uriExploded = explode('_', $matches[0]);

        $tag = end($uriExploded);

        return (int)ltrim($tag, 't');
    }
}
