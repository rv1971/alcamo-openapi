<?php

namespace alcamo\openapi;

use alcamo\json\JsonDocumentFactory;

/**
 * @brief Factory for OpenApi objects
 */
class OpenApiFactory extends JsonDocumentFactory
{
    public const DOCUMENT_CLASS = OpenApi::class;
}
