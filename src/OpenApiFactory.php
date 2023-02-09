<?php

namespace alcamo\openapi;

use alcamo\json\JsonDocumentFactory;

/// Factory for OpenApi documents
class OpenApiFactory extends JsonDocumentFactory
{
    public const DOCUMENT_CLASS = OpenApi::class;
}
