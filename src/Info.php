<?php

namespace alcamo\openapi;

class Info extends OpenApiNode
{
    public const CLASS_MAP = [
        'contact' => Contact::class,
        'license' => License::class,
        '*' => OpenApiNode::class // for extensions
    ];
}
