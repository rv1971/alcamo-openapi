<?php

namespace alcamo\openapi;

use alcamo\exception\{AbsoluteUriNeeded, DataValidationFailed};
use alcamo\ietf\Uri;
use alcamo\json\{
    JsonNode,
    RecursiveWalker,
    ReferenceResolver,
    TypedNodeDocumentTrait
};
use alcamo\json\exception\NodeNotFound;
use Opis\JsonSchema\Uri as OpisUri;
use Psr\Http\Message\UriInterface;

/**
 * @brief OpenAPI document
 *
 * To optimize JSON Schema caching, this class comes with three validators,
 * returned by
 * - getGlobalValidator()
 * - getClassValidator()
 * - getValidator()
 */
class OpenApi extends OpenApiNode
{
    use TypedNodeDocumentTrait;

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

    /// Base URI used in IDs of bundled schemas
    public const SCHEMA_BASE_URI =
        'tag:rv1971@web.de,2021:alcamo-openapi:schema:';

    /// Directory where bundled schemas are stored
    public const SCHEMA_DIR = __DIR__ . DIRECTORY_SEPARATOR
        . '..' . DIRECTORY_SEPARATOR
        . 'schemas' . DIRECTORY_SEPARATOR;

    /**
     * @brief Paths to class-independent schema files
     *
     * @note Redefinitions of this constant in child classes are ignored.
     */
    public const GLOBAL_SCHEMAS = [
        self::SCHEMA_DIR . 'openapi-3.0.json',
        self::SCHEMA_DIR . 'openapi-3.1.json'
    ];

    /// Map of OpenAPI versions to schema IDs
    public const OPENAPI_VERSIONS = [
        '3.0' => 'tag:rv1971@web.de,2021:alcamo-openapi:schema:openapi:3.0',
        '3.1' => 'https://spec.openapis.org/oas/3.1/schema/2021-05-20'
    ];

    /**
     * @brief Paths to additional schema files
     *
     * This constant may be refined in child classes.
     */
    public const SCHEMAS = [
        self::SCHEMA_BASE_URI . 'extension:info.metadata'
        => self::SCHEMA_DIR . 'extension.info.metadata.json'
    ];

    /**
     * @brief Pairs of JSON pointers and applicable schema IDs
     *
     * If no node exists for a JSON pointer, the entry is ignored.
     *
     * This constant may be refined in child classes.
     */
    public const EXTRA_VALIDATION_RULES = [
        [ '/info', self::SCHEMA_BASE_URI . 'extension:info.metadata' ]
    ];

    /// Class-independent validator
    private static $globalValidator_;

    /// Map of class names to Validator objects
    private static $validators_;

    /// First two components of OpenAPI version of this document
    private $openApiVersion_;

    /// Document-specific validator
    private $validator_;

    /**
     * @brief Global validator for all instances of all derived classes
     *
     * The OpenAPI schemas taken from @ref GLOBAL_SCHEMAS are registered
     * here. This validator should not be modified in child classes.
     */
    public static function getGlobalValidator(): Validator
    {
        if (!isset(self::$globalValidator_)) {
            self::$globalValidator_ =
                Validator::newFromSchemas(self::GLOBAL_SCHEMAS);
        }

        return self::$globalValidator_;
    }

    /**
     * @brief Class-specific validator for all instances of the current class
     *
     * Returns different objects when called from different child classes of
     * OpenApi. The additional schemas taken from @ref SCHEMAS (which may be
     * overridden in a child class) are registered here.
     */
    public static function getClassValidator(): Validator
    {
        if (!isset(self::$validators_[static::class])) {
            self::$validators_[static::class] =
                Validator::newFromSchemas(static::SCHEMAS);
        }

        return self::$validators_[static::class];
    }

    /**
     * In addition to creating a JSON document, the constructor
     * - resolves any external references
     * - performs OpenAPI 3.0 specific adjustments (see adjustForOpenApi30())
     * - validates the document
     * - validates the examples in the document
     * - validates (parts of) the document against extensions as specified
     * by @ref EXTRA_VALIDATION_RULES.
     */
    public function __construct(
        $data,
        ?self $ownerDocument = null,
        ?string $jsonPtr = null,
        ?UriInterface $baseUri = null
    ) {
        if (!Uri::isAbsolute($baseUri)) {
            /** @throw alcamo::exception::AbsoluteUriNeeded if the base URI is
             *  not absolute. An absolute URI is necessary to register the
             *  document in the validator returned by getValidator(). */
            throw new AbsoluteUriNeeded($baseUri);
        }

        parent::__construct($data, $ownerDocument, $jsonPtr, $baseUri);

        $this->resolveReferences(ReferenceResolver::RESOLVE_EXTERNAL);

        $this->openApiVersion_ =
            substr($this->openapi, 0, strrpos($this->openapi, '.'));

        if ($this->openApiVersion_ == '3.0') {
            $this->adjustForOpenApi30();
        }

        $this->validate();

        $this->validator_ = new Validator();

        $this->validator_->loader()
            ->setBaseUri(OpisUri::parse($this->getBaseUri()));

        $this->validator_->resolver()->registerRaw($this, $this->getBaseUri());

        $this->validateExamples();

        $this->validateExtensions();
    }

    /**
     * @brief Instance-specific validator
     *
     * The entire current document is registered here as a JSON schema so that
     * schemas contained in it can be used for validation. Since the loader's
     * base URI is set to the document URI, a contained schema can be
     * identified simply by a URL fragment containing a JSON pointer.
     */
    public function getValidator(): Validator
    {
        return $this->validator_;
    }

    public function resolveExternalValues(): void
    {
        foreach (
            new RecursiveWalker(
                $this,
                RecursiveWalker::JSON_OBJECTS_ONLY
            ) as $node
        ) {
            if (isset($node->externalValue)) {
                $node->resolveExternalValue();
            }
        }
    }

    /// Remove some metadata of newer schemas not supported in OpenAPI 3.0
    protected function adjustForOpenApi30(): void
    {
        foreach (
            new RecursiveWalker(
                $this,
                RecursiveWalker::JSON_OBJECTS_ONLY
            ) as $node
        ) {
            if (!($node instanceof Schema)) {
                continue;
            }

            unset($node->{'$comment'});
            unset($node->{'$id'});
            unset($node->{'$schema'});
            unset($node->{'id'});
        }
    }

    protected function validate(): void
    {
        self::getGlobalValidator()->validate(
            $this,
            self::OPENAPI_VERSIONS[$this->openApiVersion_]
        );
    }

    protected function validateExamples(): void
    {
        foreach (
            new RecursiveWalker(
                $this,
                RecursiveWalker::JSON_OBJECTS_ONLY
            ) as $node
        ) {
            if (!$node instanceof HasExampleInterface) {
                continue;
            }

            if (isset($node->example)) {
                if (isset($node->schema)) {
                    $this->validateExample(
                        $node->example,
                        $node->schema,
                        $node->getUri('example')
                    );
                } elseif ($node instanceof Schema) {
                    $this->validateExample(
                        $node->example,
                        $node,
                        $node->getUri('example')
                    );
                }
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

    /* The type hint for the second argument is JsonNode, not OpenApiNode,
     * because it may be a reference object, in which case it is not an
     * OpenApiNode. */
    protected function validateExample(
        $value,
        JsonNode $schema,
        string $uri
    ): void {
        try {
            $this->validator_->validate($value, (string)$schema->getUri());
        } catch (DataValidationFailed $e) {
            /** Ignore validation errors when $value is a string while a
             * complex type was expected, because this normally means that the
             * example is serialized non-JSON data. */
            if (
                is_string($value)
                && $e->rootCause->keyword() == 'type'
                && in_array(
                    $e->rootCause->args()['expected'],
                    ['array', 'object']
                )
            ) {
                return;
            }

            throw $e;
        }
    }

    protected function validateExtensions()
    {
        $validator = $this->getClassValidator();

        foreach (static::EXTRA_VALIDATION_RULES as $pair) {
            [ $jsonPtr, $schemaId ] = $pair;

            try {
                $validator->validate($this->getNode($jsonPtr), $schemaId);
            } catch (NodeNotFound $e) {
            }
        }
    }
}
