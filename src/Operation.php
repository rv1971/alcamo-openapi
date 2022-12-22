<?php

namespace alcamo\openapi;

use alcamo\json\JsonNode;
use Psr\Http\Message\UriInterface;

class Operation extends OpenApiNode
{
    public const CLASS_MAP = [
        'externalDocs' => ExternalDocumentation::class,
        'parameters'   => [ '*' => Parameter::class ],
        'requestBody'  => RequestBody::class,
        'responses'    => Responses::class,
        'callbacks'    => Callbacks::class,
        'security'     => [ '*' => SecurityRequirement::class ],
        'servers'      => [ '*' => Server::class ],
        '*'            => OpenApiNode::class // for extensions
    ];

    public function __construct(
        object $data,
        ?UriInterface $baseUri = null,
        ?JsonNode $ownerDocument = null,
        ?string $jsonPtr = null
    ) {
        parent::__construct($data, $baseUri, $ownerDocument, $jsonPtr);

        if (isset($this->operationId)) {
            $ownerDocument->addOperation($this);
        }
    }
}
