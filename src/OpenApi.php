<?php

namespace alcamo\openapi;

use alcamo\exception\DataValidationFailed;
use alcamo\json\{
    JsonDocument,
    JsonDocumentFactory,
    RecursiveWalker,
    ReferenceResolver
};
use Opis\JsonSchema\{Schema, Validator};
use Opis\JsonSchema\Errors\ErrorFormatter;
use Psr\Http\Message\UriInterface;

class OpenApi extends AbstractTypedJsonDocument
{
    public const SUPPORTED_VERSIONS = [ '3.0' ];

    public const VERSION_URI_PREFIX =
        'https://github.com/rv1971/alcamo-openapi?openApiVersion=';

    public const CLASS_MAP = [
        'info'         => Info::class,
        'servers'      => [ '*' => Server::class ],
        'paths'        => Paths::class,
        'components'   => Components::class,
        'security'     => [ '*' => SecurityRequirement::class ],
        'tags'         => [ '*' => Tag::class ],
        'externalDocs' => ExternalDocs::class,
        '*'            => OpenApiNode::class
    ];

    private static $validator_; ///< Validator

    public static function getValidator(): Validator
    {
        if (!isset(self::$validator_)) {
            self::$validator_ = new Validator();

            foreach (self::SUPPORTED_VERSIONS as $version) {
                self::$validator_->resolver()->registerFile(
                    self::VERSION_URI_PREFIX . $version,
                    dirname(__DIR__) . DIRECTORY_SEPARATOR
                    . 'schemas' . DIRECTORY_SEPARATOR
                    . "openapi-$version.json"
                );
            }
        }

        return self::$validator_;
    }

    public function __construct(
        $data,
        ?self $ownerDocument = null,
        ?string $jsonPtr = null,
        ?UriInterface $baseUri = null
    ) {
        parent::__construct($data, $ownerDocument, $jsonPtr, $baseUri);

        $this->resolveReferences(ReferenceResolver::RESOLVE_EXTERNAL);

        $this->validate();
    }

    public function resolveExternalValues(): void
    {
        $walker =
            new RecursiveWalker($this, RecursiveWalker::JSON_OBJECTS_ONLY);

        foreach ($walker as $object) {
            if (isset($object->externalValue)) {
                $object->resolveExternalValue();
            }
        }
    }

    private function validate(): void
    {
        $validationResult = self::getValidator()->validate(
            $this,
            self::VERSION_URI_PREFIX
            . substr($this->openapi, 0, strrpos($this->openapi, '.'))
        );

        if ($validationResult->hasError()) {
            $error = $validationResult->error();
            /** @throw alcamo::exception::DataValidationFailed if not a valid
             *  OpenApi schema. */
            throw new DataValidationFailed(
                json_encode($error->data()),
                $this->getBaseUri(),
                null,
                json_encode(
                    (new ErrorFormatter())->format($error),
                    JSON_PRETTY_PRINT
                )
            );
        }
    }
}
