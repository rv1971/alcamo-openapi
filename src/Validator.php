<?php

namespace alcamo\openapi;

use alcamo\exception\DataValidationFailed;
use alcamo\ietf\Uri;
use alcamo\json\{JsonDocumentFactory, JsonNode};
use Opis\JsonSchema\{Validator as ValidatorBase, ValidationResult};
use Opis\JsonSchema\Errors\ErrorFormatter;

/**
 * Validator that resolves schema IDs to JsonDocument objects
 */
class Validator extends ValidatorBase
{
    /**
     * @param $map Map of schema IDs to schema paths
     */
    public static function newFromSchemas(array $map): self
    {
        $validator = new static();

        $factory = new JsonDocumentFactory();

        foreach ($map as $id => $path) {
            $validator->resolver()->registerRaw(
                $factory->createFromUrl(Uri::newFromFilesystemPath($path)),
                $id
            );
        }

        return $validator;
    }

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
