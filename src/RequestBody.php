<?php

namespace alcamo\openapi;

class RequestBody extends OpenApiNode
{
    public const CLASS_MAP = [
        'content' => MediaTypes::class,
        '*'       => OpenApiNode::class // for extensions
    ];
}
