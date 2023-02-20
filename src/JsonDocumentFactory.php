<?php

namespace alcamo\openapi;

use alcamo\json\JsonDocumentFactory as JsonDocumentFactoryBase;

/// Factory for documents made of OpenAPI nodes
class JsonDocumentFactory extends JsonDocumentFactoryBase
{
    public const DOCUMENT_CLASS = JsonDocument::class;
}
