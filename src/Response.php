<?php

namespace alcamo\openapi;

class Response extends OpenApiNode
{
    public const CLASS_MAP = [
        'headers' => Headers::class,
        'content' => MediaTypes::class,
        'links'   => Links::class,
        '*'       => OpenApiNode::class // for extensions
    ];
}
