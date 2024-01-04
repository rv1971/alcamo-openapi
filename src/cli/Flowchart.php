<?php

namespace alcamo\openapi\cli;

use alcamo\json\JsonPtr;
use alcamo\openapi\OpenApi;

class Flowchart
{
    public const NO_EDGE_LABELS = 1;

    public const NO_HTTP_METHOD_LABELS = 2;

    public const NO_SPECIAL_NODES = 4;

    public const HTTP_METHODS = [
        'get', 'put', 'post', 'delete', 'options', 'head', 'path', 'trace'
    ];

    private $openApi_;    ///< OpenApi
    private $options_;    ///< int
    private $baseUrl_;    ///< ?string
    private $nodes_ = []; ///< array of AbstractFlowchartNode
    private $edges_ = []; ///< array of AbstractFlowchartEdge

    public function __construct(
        OpenApi $openApi,
        int $options,
        ?string $baseUrl
    ) {
        $this->openApi_ = $openApi;

        $this->options_ = (int)$options;

        $this->baseUrl_ = $baseUrl;

        $this->collectNodesAndEdges();

        if (!($this->options_ & self::NO_SPECIAL_NODES)) {
            $this->addSpecialNodes();
        }
    }

    public function getOpenApi(): OpenApi
    {
        return $this->openApi_;
    }

    public function getOptions(): int
    {
        return $this->options_;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl_;
    }

    public function getNodes(): array
    {
        return $this->nodes_;
    }

    public function getEdges(): array
    {
        return $this->edges_;
    }

    public function addNode(AbstractFlowchartNode $node): void
    {
        $this->nodes_[$node->getId()] = $node;
    }

    public function addEdge(AbstractFlowchartEdge $edge): void
    {
        $this->edges_[$edge->getId()] = $edge;
    }

    public function createDotCode(?string $include = null): string
    {
        $result = [ 'digraph {', (string)$include ];

        foreach ($this->nodes_ as $node) {
            $result[] = $node->createDotCode();
        }

        foreach ($this->edges_ as $edge) {
            $result[] = $edge->createDotCode();
        }

        $result[] = '}';

        return implode("\n\n", $result);
    }

    protected function collectNodesAndEdges(): void
    {
        foreach ($this->openApi_->getRoot()->paths as $path) {
            foreach (static::HTTP_METHODS as $method) {
                if (!isset($path->$method)) {
                    continue;
                }

                foreach ($path->$method->responses as $response) {
                    if (!isset($response->links)) {
                        continue;
                    }

                    $fromOperation = $path->$method;

                    if (
                        isset($fromOperation->{'x-graphviz-hints'})
                        && $fromOperation->{'x-graphviz-hints'}
                            ->hasFlag('ignore')
                    ) {
                        continue;
                    }

                    $fromNode =
                        $this->nodes_[
                            OperationFlowchartNode::jsonPtr2Id(
                                $fromOperation->getJsonPtr()
                            )
                        ]
                        ?? null;

                    if (!isset($fromNode)) {
                        $fromNode = new OperationFlowchartNode(
                            $this,
                            $fromOperation
                        );
                    }

                    foreach ($response->links as $link) {
                        if (isset($link->operationRef)) {
                            $toOperationPtr = JsonPtr::newFromString(
                                ltrim($link->operationRef, '#')
                            );
                            $toOperation =
                                $this->openApi_->getNode($toOperationPtr);
                        } else {
                            $toOperation = $this->openApi_
                                ->getOperation($link->operationId);
                            $toOperationPtr = $toOperation->getJsonPtr();
                        }

                        if (
                            isset($toOperation->{'x-graphviz-hints'})
                            && $toOperation->{'x-graphviz-hints'}
                                ->hasFlag('ignore')
                        ) {
                            continue;
                        }

                        $toNode =
                            $this->nodes_[
                                OperationFlowchartNode::jsonPtr2Id(
                                    $toOperationPtr
                                )
                            ]
                            ?? null;

                        if (!isset($toNode)) {
                            $toNode = new OperationFlowchartNode(
                                $this,
                                $toOperation
                            );
                        }

                        $edge = new LinkFlowchartEdge(
                            $this,
                            $fromNode,
                            $toNode,
                            $link
                        );
                    }
                }
            }
        }
    }

    public function addSpecialNodes(): void
    {
        $startNode = new SpecialFlowchartNode($this, 'START');

        $endNode = new SpecialFlowchartNode($this, 'END');

        foreach ($this->nodes_ as $node) {
            if ($node instanceof OperationFlowchartNode) {
                $hints = $node->getHints();

                if (
                    !$node->getInEdges()
                    || isset($hints->{'start-edge-label'})
                    || isset($hints) && $hints->hasFlag('start-node')
                ) {
                    $edge = new SpecialFlowchartEdge($this, $startNode, $node);
                }

                if (
                    !$node->getOutEdges()
                    || isset($hints->{'end-edge-label'})
                    || isset($hints) && $hints->hasFlag('end-node')
                ) {
                    $edge = new SpecialFlowchartEdge($this, $node, $endNode);
                }
            }
        }
    }
}
