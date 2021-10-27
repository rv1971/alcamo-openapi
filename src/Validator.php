<?php

namespace alcamo\openapi;

use alcamo\exception\DataValidationFailed;
use alcamo\ietf\UriFactory;
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
            $schemaDocument = $factory->createFromUrl(
                (new UriFactory())->createFromFilesystemPath($path)
            );

            $id = $schemaDocument->{'$id'};

            if (!is_numeric($key) && $key != $id) {
                /** @throw alcamo::exception::DataValidationFailed if the key
                 *  to a schema in $schemas is not numeric and is not equal to
                 *  the schema ID. */
                throw (new DataValidationFailed())->setMessageContext(
                    [
                        'inData' => $schemaDocument,
                        'atUri' => $schemaDocument->getUri('$id'),
                        'extraMessage' =>
                        "schema id \"$id\" differs from key \"$key\""
                    ]
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
             *  fails. */

            $context = [
                'inData' => $rootCause->data()->value(),
                'error' => $error,
                'rootCause' => $rootCause,
                'extraMessage' => lcfirst(
                    (new ErrorFormatter())->formatErrorMessage($rootCause)
                )
            ];

            if ($data instanceof JsonNode) {
                $context['atUri'] = $data->getUri(
                    implode(
                        '/',
                        str_replace(
                            [ '~', '/' ],
                            [ '~0', '~1' ],
                            $rootCause->data()->fullpath()
                        )
                    )
                );
            }

            throw (new DataValidationFailed())->setMessageContext($context);
        }

        return $validationResult;
    }
}
