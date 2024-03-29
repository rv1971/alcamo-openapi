<?php

namespace alcamo\openapi;

use alcamo\json\{JsonDocument, JsonNode, JsonPtr};

class Operation extends OpenApiNode
{
    public const CLASS_MAP = [
        'externalDocs'     => ExternalDocumentation::class,
        'parameters'       => [ '*' => Parameter::class ],
        'requestBody'      => RequestBody::class,
        'responses'        => Responses::class,
        'callbacks'        => Callbacks::class,
        'security'         => [ '*' => SecurityRequirement::class ],
        'servers'          => [ '*' => Server::class ],
        'x-graphviz-hints' => GraphvizHints::class,
        '*'                => OpenApiNode::class // for extensions
    ];

    public function __construct(
        object $data,
        JsonDocument $ownerDocument,
        JsonPtr $jsonPtr,
        ?JsonNode $parent = null
    ) {
        parent::__construct($data, $ownerDocument, $jsonPtr, $parent);

        if (isset($this->operationId)) {
            $ownerDocument->addOperation($this);
        }
    }
}
