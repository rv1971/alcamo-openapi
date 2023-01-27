<?php

namespace alcamo\openapi;

use alcamo\exception\{DataValidationFailed, Unsupported};
use alcamo\json\JsonPtr;

class Link extends OpenApiNode
{
    private $target_; /// Operation

    public const CLASS_MAP = [
        'server' => Server::class,
        '*'      => OpenApiNode::class // for extensions
    ];

    public function getTarget(): Operation
    {
        if (!isset($this->target_)) {
            if (isset($this->operationId)) {
                try {
                    $this->target_ = $this->getOwnerDocument()
                        ->getOperation($this->operationId);
                } catch (\Throwable $e) {
                    throw (new DataValidationFailed())->setMessageContext(
                        [
                            'atUri' => $this->getUri(),
                            'inData' => $this->operationId,
                            'extraMessage' => "unknown operation ID \"$this->operationId\""
                        ]
                    );
                }
            } elseif (isset($this->operationRef)) {
                if ($this->operationRef[0] == '#') {
                    /** @throw alcamo::json::exception::NodeNotFound if there
                     *  is no node for the given local URL. */
                    $this->target_ = $this->getOwnerDocument()->getNode(
                        JsonPtr::newFromString(substr($this->operationRef, 1))
                    );
                } else {
                    /** @throw alcamo::json::exception::Unsupported if
                     *  `operationRef` points to a different document. (A good
                     *  implementation should maintain a cache of already
                     *  loaded JSON documents here.)*/
                    throw (new Unsupported())->setMessageContext(
                        [
                            'atUri' => $this->getUri(),
                            'feature' => 'links to other documents',
                            'inData' => $this->operationRef
                        ]
                    );
                }
            }
        }

        return $this->target_;
    }
}
