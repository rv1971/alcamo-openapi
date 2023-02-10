<?php

namespace alcamo\openapi;

use alcamo\json\{JsonDocumentInterface, TypedNodeDocumentTrait};

/// Document made of OpenAPI nodes
class JsonDocument extends OpenApiNode implements JsonDocumentInterface
{
    use TypedNodeDocumentTrait;
}
