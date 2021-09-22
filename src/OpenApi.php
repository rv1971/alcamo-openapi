<?php

namespace alcamo\openapi;

use alcamo\exception\DataValidationFailed;
use alcamo\json\{JsonDocument, JsonDocumentFactory, ReferenceResolver};
use Opis\JsonSchema\{
    Validator
};
use Psr\Http\Message\UriInterface;
use SebastianBergmann\Exporter\Exporter;

class OpenApi extends AbstractTypedJsonDocument
{
    public const CLASS_MAP = [
        'info'         => Info::class,
        'servers'      => Server::class,
        'paths'        => Paths::class,
        'components'   => Components::class,
        'security'     => Security::class,
        'tags'         => Tag::class,
        'externalDocs' => ExternalDocs::class,
        '*'            => OpenApiNode::class
    ];

    private static $openApiSchema_; ///< JsonDocument

    public static function getOpenApiSchema(): JsonDocument
    {
        if (!isset(self::$openApiSchema_)) {
            self::$openApiSchema_ = (new JsonDocumentFactory())->createFromUrl(
                dirname(__DIR__) . DIRECTORY_SEPARATOR
                . 'schemas' . DIRECTORY_SEPARATOR
                . 'openapi-3.0.json'
            );

            self::$openApiSchema_ = self::$openApiSchema_->resolveReferences(
                ReferenceResolver::RESOLVE_EXTERNAL
            );
        }

        return self::$openApiSchema_;
    }

    public function __construct(
        $data,
        ?self $ownerDocument = null,
        ?string $jsonPtr = null,
        ?UriInterface $baseUri = null
    ) {
        parent::__construct($data, $ownerDocument, $jsonPtr, $baseUri);

        $copy = $this->createDeepCopy();

        $copy->resolveReferences();

        $validator = new Validator();

        $copy2 = json_decode(json_encode($copy));

        $schema2 = json_decode(json_encode(self::getOpenApiSchema()));

        $validationResult =
            $validator->validate($copy2, $schema2);

        if ($validationResult->hasError()) {
            $error = $validationResult->error();

            /** @throw alcamo::exception::DataValidationFailed if not a valid
             *  OpenApi schema. */
            throw new DataValidationFailed(
                json_encode($error->data()),
                $baseUri,
                null,
                $error->message()
                . ", at keyword \"{$error->keyword()}\" with args "
                . (new Exporter())->export($error->args())
                . ", data: " . json_encode($error->data())
            );
        }
    }
}
