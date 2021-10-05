<?php

namespace alcamo\openapi;

use alcamo\exception\DataValidationFailed;
use alcamo\ietf\Uri;
use alcamo\json\{SchemaDocumentFactory, JsonNode};
use Opis\JsonSchema\{Validator as ValidatorBase, ValidationResult};
use Opis\JsonSchema\Errors\ErrorFormatter;

/**
 * Validator with some convenience methods
 *
 * @todo Move this to alcamo-json. The main reason why it is still here is
 * that Cygwin does not yet provide php 7.4, and therefore ugly forks of some
 * Opis packages are needed to make Opis run on Cygwin. I would avoid to make
 * more packages depend on these forks.
 */
class Validator extends ValidatorBase
{
    /**
     * @param $schemas Filesystem paths to schema files. Each schema file must
     * have an `$id` property.
     *
     * Schemas are stored in the validator as alcamo::json::SchemaDocument
     * objects and can be retrieved by ID via resolver()->resolve().
     */
    public static function newFromSchemas(iterable $schemas): self
    {
        $validator = new static();

        $factory = new SchemaDocumentFactory();

        foreach ($schemas as $key => $path) {
            $schemaDocument =
                $factory->createFromUrl(Uri::newFromFilesystemPath($path));

            $id = $schemaDocument->{'$id'};

            if (!is_numeric($key) && $key != $id) {
                /** @throw alcamo::exception::DataValidationFailed if the key
                 *  to a schema in $schemas is not numeric and is not equal to
                 *  the schema ID. */
                throw new DataValidationFailed(
                    json_encode($schemas),
                    null,
                    null,
                    "; key \"$key\" differs from schema id \"$id\""
                );
            }

            $validator->resolver()->registerRaw($schemaDocument, $id);
        }

        return $validator;
    }

    /**
     * Identical to the parent's validate() except that an exception is thrown
     * if validation fails.
     */
    public function validate(
        $data,
        $schema,
        ?array $globals = null,
        ?array $slots = null
    ): ValidationResult {
        $validationResult = parent::validate($data, $schema, $globals, $slots);

        if ($validationResult->hasError()) {
            $error = $validationResult->error();

            for (
                $rootCause = $error;
                $rootCause->subErrors();
                $rootCause = $rootCause->subErrors()[0]
            ) {
            }

            /** @throw alcamo::exception::DataValidationFailed if validation
             *  fails. Store the validation error in the property `error` and
             *  the root cause error in the property `rootCause` of the
             *  exception object. */
            $e = new DataValidationFailed(
                json_encode($error->data()->value()),
                $data instanceof JsonNode ? $data->getUri() : null,
                null,
                '; ' . lcfirst(
                    (new ErrorFormatter())->formatErrorMessage($rootCause)
                )
            );

            $e->error = $error;
            $e->rootCause = $rootCause;

            throw $e;
        }

        return $validationResult;
    }
}
