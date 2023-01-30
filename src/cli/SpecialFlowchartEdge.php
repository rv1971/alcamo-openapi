<?php

namespace alcamo\openapi\cli;

class SpecialFlowchartEdge extends AbstractFlowchartEdge
{
    public function createDotCode(): string
    {
        $attrs = [
            'id' => $this->getId(),
            'class' => 'special-edge'
        ];

        return parent::createDotCode() . $this->createDotAttrs($attrs);
    }
}
