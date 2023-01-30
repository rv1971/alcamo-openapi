<?php

namespace alcamo\openapi\cli;

use alcamo\openapi\Operation;

class OperationFlowchartNode extends AbstractFlowchartNode
{
    private $operation_; ///< Operation
    private $hints_;     ///< JsonNode|stdClass

    public function __construct(Flowchart $flowchart, Operation $operation)
    {
        [ , $path, $method ] = $operation->getJsonPtr();

        $id = $this->name2Id(substr($path, 1) . '_' . $method);

        $this->hints_ = $operation->{'x-graphviz-hints'} ?? (object)[];

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

    public function createDotCode(): string
    {
        $attrs = [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'class' => 'operation-node'
        ];

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

        return $this->getId() . $this->createDotAttrs($attrs);
    }
}
