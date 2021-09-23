<?php

namespace alcamo\openapi;

class PathItem extends OpenApiNode
{
    public const CLASS_MAP = [
        'get'        => Operation::class,
        'put'        => Operation::class,
        'post'       => Operation::class,
        'delete'     => Operation::class,
        'options'    => Operation::class,
        'head'       => Operation::class,
        'patch'      => Operation::class,
        'trace'      => Operation::class,
        'servers'    => [ '*' => Server::class ],
        'parameters' => [ '*' => Parameter::class ]
    ];
}
