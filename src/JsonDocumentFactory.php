<?php

namespace alcamo\openapi;

use alcamo\json\JsonDocumentFactory;

/// Factory for documents made of OpenAPI nodes
class JsonDocumentFactory extends JsonDocumentFactory
{
    public const DOCUMENT_CLASS = Document::class;
}
