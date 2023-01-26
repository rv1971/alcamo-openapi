<?php

namespace alcamo\openapi;

class Tag extends OpenApiNode
{
    public const CLASS_MAP = [
        'externalDocs' => ExternalDocumentation::class,
        '*'            => OpenApiNode::class // for extensions
    ];
}
