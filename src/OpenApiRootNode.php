<?php

namespace alcamo\openapi;

class OpenApiRootNode extends OpenApiNode
{
    public const CLASS_MAP = [
        'info'         => Info::class,
        'servers'      => [ '*' => Server::class ],
        'paths'        => Paths::class,
        'components'   => Components::class,
        'security'     => [ '*' => SecurityRequirement::class ],
        'tags'         => [ '*' => Tag::class ],
        'externalDocs' => ExternalDocs::class,
        '*'            => OpenApiNode::class // for extensions
    ];
}
