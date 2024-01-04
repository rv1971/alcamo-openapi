<?php

namespace alcamo\openapi\cli;

class SpecialFlowchartEdge extends AbstractFlowchartEdge
{
    public function createDotCode(): string
    {
        $attrs = [
            'class' => 'special-edge'
        ];

        if ($this->getFromNode() instanceof OperationFlowchartNode) {
            $hints = $this->getFromNode()->getHints();

            if (isset($hints->{'end-edge-class'})) {
                $attrs['class'] .= ' ' . $hints->{'end-edge-class'};
            }

            if (isset($hints->{'end-edge-label'})) {
                $attrs['label'] = $hints->{'end-edge-label'};
            }
        } else {
            $hints = $this->getToNode()->getHints();

            if (isset($hints->{'start-edge-class'})) {
                $attrs['class'] .= ' ' . $hints->{'start-edge-class'};
            }

            if (isset($hints->{'start-edge-label'})) {
                $attrs['label'] = $hints->{'start-edge-label'};
            }
        }

        return $this->createDotCodeFromAttrs($attrs);
    }
}
