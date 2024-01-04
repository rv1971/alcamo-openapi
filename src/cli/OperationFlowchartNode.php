<?php

namespace alcamo\openapi\cli;

use alcamo\json\JsonPtr;
use alcamo\openapi\{GraphvizHints, Operation};

class OperationFlowchartNode extends AbstractFlowchartNode
{
    private $operation_; ///< Operation
    private $hints_;     ///< GraphvizHints

    public static function jsonPtr2Id(JsonPtr $jsonPtr): string
    {
        [ , $path, $method ] = $jsonPtr;

        return self::name2Id(substr($path, 1) . '_' . $method);
    }

    public function __construct(Flowchart $flowchart, Operation $operation)
    {
        $id = self::jsonPtr2Id($operation->getJsonPtr());

        $this->hints_ = $operation->{'x-graphviz-hints'}
            ?? new GraphvizHints(
                (object)[],
                $operation->getOwnerDocument(),
                $operation->getJsonPtr()->appendSegment('x-graphviz-hints')
            );

        [ , $path, $method ] = $operation->getJsonPtr();

        $label = $this->hints_->label
            ?? ($flowchart->getOptions() & Flowchart::NO_HTTP_METHOD_LABELS
                ? $path
                : "$path $method");

        parent::__construct($flowchart, $id, $label);

        $this->operation_ = $operation;
    }

    public function getOperation(): ?Operation
    {
        return $this->operation_;
    }

    public function getHints(): GraphvizHints
    {
        return $this->hints_;
    }

    public function createDotCode(): string
    {
        $attrs = [ 'class' => 'operation-node' ];

        if (isset($this->operation_->summary)) {
            $attrs['tooltip'] = $this->operation_->summary;
        } elseif (isset($this->operation_->getParent()->summary)) {
            $attrs['tooltip'] = $this->operation_->getParent()->summary;
        }

        if ($this->getFlowchart()->getBaseUrl()) {
            $attrs['URL'] = $this->getFlowchart()->getBaseUrl()
                . '#' . $this->operation_->getJsonPtr();
            $attrs['target'] = '_top';
        }

        if (isset($this->hints_->class)) {
            $attrs['class'] .= ' ' . $this->hints_->class;
        }

        return $this->createDotCodeFromAttrs($attrs);
    }
}
