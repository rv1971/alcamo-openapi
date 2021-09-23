<?php

namespace alcamo\openapi;

class SecurityScheme extends OpenApiNode
{
    public const CLASS_MAP = [
        'flows' => OAuthFlows::class
    ];
}
