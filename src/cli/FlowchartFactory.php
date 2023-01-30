<?php

namespace alcamo\openapi\cli;

use alcamo\openapi\OpenApi;

class FlowchartFactory
{
    public function create(
        OpenApi $openApi,
        int $options,
        ?string $baseUrl
    ): Flowchart {
        return new Flowchart($openApi, $options, $baseUrl);
    }
}
