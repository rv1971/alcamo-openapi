<?php

namespace alcamo\openapi;

class OpenApi extends AbstractTypedJsonDocument
{
    public const CLASS_MAP = [
        'info'         => Info::class,
        'servers'      => Server::class,
        'paths'        => Paths::class,
        'components'   => Components::class,
        'security'     => Security::class,
        'tags'         => Tag::class,
        'externalDocs' => ExternalDocs::class,
        '*'            => OpenApiNode::class
    ];
}
