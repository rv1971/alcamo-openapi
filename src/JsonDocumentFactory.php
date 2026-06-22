<?php

namespace alcamo\openapi;

use alcamo\json\JsonDocumentFactory as BaseJsonDocumentFactory;

/// Factory for documents made of OpenAPI nodes
class JsonDocumentFactory extends BaseJsonDocumentFactory
{
    public const DOCUMENT_CLASS = JsonDocument::class;
}
