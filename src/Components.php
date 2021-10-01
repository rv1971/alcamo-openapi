<?php

namespace alcamo\openapi;

class Components extends OpenApiNode
{
    public const CLASS_MAP = [
        'schemas'         => Schemas::class,
        'responses'       => Responses::class,
        'parameters'      => Parameters::class,
        'examples'        => Examples::class,
        'requestBodies'   => RequestBodies::class,
        'headers'         => Headers::class,
        'securitySchemes' => SecuritySchemes::class,
        'links'           => Links::class,
        'callbacks'       => Callbacks::class,
        '*'               => OpenApiNode::class // for extensions
    ];
}
