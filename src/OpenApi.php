<?php

namespace alcamo\openapi;

use alcamo\exception\{AbsoluteUriNeeded, DataValidationFailed};
use alcamo\ietf\Uri;
use alcamo\json\{
    JsonNode,
    RecursiveWalker,
    ReferenceResolver
};
use alcamo\rdfa\RdfaData;
use Psr\Http\Message\UriInterface;

class OpenApi extends AbstractTypedJsonDocument
{
    public const SCHEMA_BASE_URI =
        'tag:rv1971@web.de,2021,2021:alcamo-openapi:schema:';

    public const SCHEMA_DIR = __DIR__ . DIRECTORY_SEPARATOR
        . '..' . DIRECTORY_SEPARATOR
        . 'schemas' . DIRECTORY_SEPARATOR;

    /**
     * @brief Class-independent schemas
     *
     * @note Redefinitions of this constant in child classes are ignored.
     *
     * Map of schema IDs to schema paths in the file system.
     */
    public const GLOBAL_SCHEMAS = [
        self::SCHEMA_BASE_URI . 'openapi:3.0'
        => self::SCHEMA_DIR . 'openapi-3.0.json'
    ];

    /**
     * @brief Additional schemas
     *
     * This constant may be refined in child classes.
     *
     * Map of schema IDs to schema paths in the file system.
     */
    public const SCHEMAS = [
        self::SCHEMA_BASE_URI . 'extension:info.metadata'
        => self::SCHEMA_DIR . 'extension.info.metadata.json'
    ];

    /// Map of JSON pointers to applicable schema IDs
    public const JSON_PTR2SCHEMA_ID = [
        '/info' => self::SCHEMA_BASE_URI . 'extension:info.metadata'
    ];

    public const OPENAPI_URI = 'https://swagger.io/specification/';

    public const DEFAULT_RDFA_DATA = [ 'dc:type' => 'Text' ];

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

    private static $globalValidator_; ///< Validator

    private static $validators_; ///< Map of class names to Validator objects

    private $validator_; ///< Validator

    private $rdfaData_;  ///< RdfaData

    public static function getGlobalValidator(): Validator
    {
        if (!isset(self::$globalValidator_)) {
            self::$globalValidator_ =
                Validator::newFromSchemas(self::GLOBAL_SCHEMAS);
        }

        return self::$globalValidator_;
    }

    public static function getValidator(): Validator
    {
        if (!isset(self::$validators_[static::class])) {
            self::$validators_[static::class] =
                Validator::newFromSchemas(static::SCHEMAS);
        }

        return self::$validators_[static::class];
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

        $this->resolveReferences(ReferenceResolver::RESOLVE_EXTERNAL);

        $this->validator_ = new Validator();

        $this->validator_->resolver()->registerRaw($this, $this->getBaseUri());

        $this->validate();

        $this->validateExamples();

        $this->validateExtensions();
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

    public function getRdfaData(): RdfaData
    {
        if (!isset($this->rdfaData_)) {
            $rdfaProps = [
                'dc:title' => $this->info->title,
                'owl:versionInfo' => $this->info->version,
                'dc:conformsTo' => [
                    [ self::OPENAPI_URI, "OpenAPI $this->openapi" ]
                ]
            ];

            if (isset($this->info->contact)) {
                $rdfaProps['dc:creator'] = $this->info->contact->toDcCreator();
            }

            $this->rdfaData_ = RdfaData::newFromIterable(
                $rdfaProps + static::DEFAULT_RDFA_DATA
            );

            $rdfaProps = [];

            foreach ($this->info as $prop => $value) {
                if (substr($prop, 0, 5) == 'x-dc-') {
                    $rdfaProps['dc:' . substr($prop, 5)] = $value;
                }
            }

            $this->rdfaData_ = $this->rdfaData_->add(
                RdfaData::newFromIterable($rdfaProps)
            );
        }

        return $this->rdfaData_;
    }

    protected function validate(): void
    {
        self::getGlobalValidator()->validate(
            $this,
            self::SCHEMA_BASE_URI . 'openapi:'
            . substr($this->openapi, 0, strrpos($this->openapi, '.'))
        );
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

    /* The type hint for the second argument is JsonNode, not OpenApiNode,
     * because it may be a reference object, in which case it is provided as a
     * JsonNode. */
    protected function validateExample(
        $value,
        JsonNode $schema,
        string $uri
    ): void {
        try {
            $this->validator_->validate($value, (string)$schema->getUri());
        } catch (DataValidationFailed $e) {
            /* Ignore validation errors when $value is a string while a
             * complex type was expected, because this normally means that the
             * example is serialized non-JSON data. */
            if (
                is_string($value)
                && $e->rootCause->keyword() == 'type'
                && in_array($e->rootCause->args()['expected'], ['array', 'object'])
            ) {
                return;
            }

            throw $e;
        }
    }

    protected function validateExtensions()
    {
        $validator = $this->getValidator();

        foreach (static::JSON_PTR2SCHEMA_ID as $jsonPtr => $schemaId) {
            $validator->validate($this->getNode($jsonPtr), $schemaId);
        }
    }
}
