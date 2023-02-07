<?php

namespace alcamo\openapi;

use alcamo\json\{JsonDocumentInterface, JsonNode, JsonPtr};
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
        JsonDocumentInterface $ownerDocument,
        JsonPtr $jsonPtr,
        ?JsonNode $parent = null
    ) {
        parent::__construct($data, $ownerDocument, $jsonPtr, $parent);

        if (isset($this->operationId)) {
            $ownerDocument->addOperation($this);
        }
    }
}
