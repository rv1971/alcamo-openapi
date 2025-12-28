<?php

namespace alcamo\openapi;

use alcamo\rdfa\{Node as RdfaNode, RdfaData};

class Info extends OpenApiNode
{
    public const CLASS_MAP = [
        'contact' => Contact::class,
        'license' => License::class,
        '*' => OpenApiNode::class // for extensions
    ];

    /// sprintf()-format to create URIs for `dc:conformsTo`
    public const OPEN_API_VERSION_URI_FORMAT =
        'https://github.com/OAI/OpenAPI-Specification/blob/main/versions/%s.md';

    /// RDFa data to include always
    public const DEFAULT_RDFA_DATA = [ 'dc:type' => [ 'dc:type', 'Text' ] ];

    /// RdfaData obtained from this node
    private $rdfaData_;

    public function getRdfaData(): RdfaData
    {
        if (!isset($this->rdfaData_)) {
            $openApiVersion = $this->getParent()->openapi;

            $rdfaProps = [
                [ 'dc:title', $this->title ],
                [ 'owl:versionInfo', $this->version ],
                [
                    'dc:conformsTo',
                    new RdfaNode(
                        sprintf(
                            self::OPEN_API_VERSION_URI_FORMAT,
                            $openApiVersion
                        ),
                        RdfaData::newFromIterable(
                            [ [ 'dc:title', "OpenAPI $openApiVersion" ] ]
                        )
                    )
                ]
            ];

            if (isset($this->contact)) {
                $rdfaProps[] = [ 'dc:creator', $this->contact->toDcCreator() ];
            }

            $this->rdfaData_ = RdfaData::newFromIterable(
                $rdfaProps + static::DEFAULT_RDFA_DATA
            );

            $rdfaProps = [];

            foreach ($this as $prop => $value) {
                if (substr($prop, 0, 5) == 'x-dc-') {
                    $rdfaProps[] = ['dc:' . substr($prop, 5), $value ];
                }
            }

            /** RdfaData::add() needed to handle the possible case of multiple
             *  values for the same property. */
            $this->rdfaData_ = $this->rdfaData_->add(
                RdfaData::newFromIterable($rdfaProps)
            );
        }

        return $this->rdfaData_;
    }
}
