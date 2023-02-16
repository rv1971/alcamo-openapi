<?php

namespace alcamo\openapi;

use alcamo\json\TypedNodeDocument;

/// Document made of OpenAPI nodes
class JsonDocument extends TypedNodeDocument
{
    public const NODE_CLASS = OpenApiNode::class;
}
