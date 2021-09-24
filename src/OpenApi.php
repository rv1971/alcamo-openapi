<?php

namespace alcamo\openapi;

use alcamo\exception\{AbsoluteUriNeeded, DataValidationFailed};
use alcamo\ietf\Uri;
use alcamo\json\{
    JsonDocument,
    JsonDocumentFactory,
    JsonNode,
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

    private $localValidator_; ///< Validator

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
        if (!Uri::isAbsolute($baseUri)) {
            throw new AbsoluteUriNeeded($baseUri);
        }

        parent::__construct($data, $ownerDocument, $jsonPtr, $baseUri);

        $this->localValidator_ = new Validator();

        $this->localValidator_->resolver()
            ->registerRaw($this, $this->getBaseUri());

        $this->resolveReferences(ReferenceResolver::RESOLVE_EXTERNAL);

        $this->validate();

        $this->validateExamples();
    }

    public function resolveExternalValues(): void
    {
        $walker =
            new RecursiveWalker($this, RecursiveWalker::JSON_OBJECTS_ONLY);

        foreach ($walker as $node) {
            if (isset($node->externalValue)) {
                $node->resolveExternalValue();
            }
        }
    }

    protected function validate(): void
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
                '; ' . json_encode(
                    (new ErrorFormatter())->format($error),
                    JSON_PRETTY_PRINT
                )
            );
        }
    }

    protected function validateExamples(): void
    {
        $walker =
            new RecursiveWalker($this, RecursiveWalker::JSON_OBJECTS_ONLY);

        foreach ($walker as $node) {
            if (isset($node->example)) {
                $this->validateExample(
                    $node->example,
                    $node->schema ?? $node,
                    $node->getUri('example')
                );
            } elseif (isset($node->examples) && isset($node->schema)) {
                foreach ($node->examples as $example) {
                    if (isset($example->{'$ref'})) {
                        $example = $example->createDeepCopy()
                            ->resolveReferences();
                    }

                    $mediaType = $example->getExternalValueMediaType();

                    // do not validate external values other than JSON
                    if (
                        !isset($mediaType) || $mediaType == 'application/json'
                    ) {
                        $this->validateExample(
                            $example->getValue(),
                            $node->schema,
                            $example->getUri(
                                isset($example->value)
                                ? 'value'
                                : 'externalValue'
                            )
                        );
                    }
                }
            }
        }
    }

    /* The second argument may be a reference object, in which case it is
     * provided as a JsonNode and not as an OpenApiNode. */
    protected function validateExample(
        $value,
        JsonNode $schema,
        string $uri
    ) {
        $schemaId = (string)$schema->getUri();

        $validationResult =
            $this->localValidator_->validate($value, $schemaId);

        if ($validationResult->hasError()) {
            $error = $validationResult->error();

            /** Ignore an error on a string when expecting an object or array
             * because the string is then likely to be the serialization of
             * non-JSON data. */
            if (is_string($value)) {
                while ($error->keyword() == '$ref') {
                    $error = $error->subErrors()[0];
                }

                if (
                    $error->keyword() == 'type'
                    && in_array($error->args()['expected'], ['array', 'object'])
                ) {
                    return;
                }
            }

            /** @throw alcamo::exception::DataValidationFailed if an example
             *  is not valid. */
            throw new DataValidationFailed(
                json_encode($error->data()),
                $uri,
                null,
                '; ' . json_encode(
                    (new ErrorFormatter())->formatOutput($error, 'verbose'),
                    JSON_PRETTY_PRINT
                )
            );
        }
    }
}
