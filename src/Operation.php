<?php

namespace alcamo\openapi;

class Operation extends OpenApiNode
{
    public const CLASS_MAP = [
        'externalDocs' => ExternalDocumentation::class,
        'parameters'   => [ '*' => Parameter::class ],
        'requestBody'  => RequestBody::class,
        'responses'    => Responses::class,
        'callbacks'    => Callbacks::class,
        'security'     => [ '*' => SecurityRequirement::class ],
        'servers'      => [ '*' => Server::class ]
    ];
}
