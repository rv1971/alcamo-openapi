<?php

namespace alcamo\openapi;

class OAuthFlows extends OpenApiNode
{
    public const CLASS_MAP = [
        'implicit'          => OAuthFlow::class,
        'password'          => OAuthFlow::class,
        'clientCredentials' => OAuthFlow::class,
        'authorizationCode' => OAuthFlow::class,
        '*'                 => OpenApiNode::class // for extensions
    ];
}
