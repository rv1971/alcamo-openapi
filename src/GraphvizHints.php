<?php

namespace alcamo\openapi;

class GraphvizHints extends OpenApiNode
{
    public function hasFlag(string $flag): bool
    {
        return isset($this->flags) && in_array($flag, $this->flags);
    }
}
