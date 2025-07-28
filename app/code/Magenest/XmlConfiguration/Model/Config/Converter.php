<?php

namespace Magenest\XmlConfiguration\Model\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $result = [];

        $resourcesNode = $source->getElementsByTagName('resources')->item(0);
        if ($resourcesNode) {
            $result['resources'] = $this->convertResourceNode($resourcesNode);
        }

        return $result;
    }

    private function convertResourceNode(\DOMNode $node)
    {
        $resources = [];

        foreach ($node->childNodes as $resourceNode) {
            if ($resourceNode->nodeType !== XML_ELEMENT_NODE || $resourceNode->nodeName !== 'resource') {
                continue;
            }

            $resourceData = [
                'id' => $resourceNode->attributes->getNamedItem('id')->nodeValue,
                'title' => $resourceNode->attributes->getNamedItem('title')->nodeValue,
                'sort_order' => $resourceNode->attributes->getNamedItem('sortOrder')->nodeValue,
                'customer_groups' => [],
                'children' => [],
            ];

            foreach ($resourceNode->childNodes as $child) {
                if ($child->nodeName === 'customer_group') {
                    $resourceData['customer_groups'][] = $child->nodeValue;
                }

                if ($child->nodeName === 'resource') {
                    $resourceData['children'][] = $this->convertSingleResourceNode($child);
                }
            }

            $resources[] = $resourceData;
        }

        return $resources;
    }

    private function convertSingleResourceNode(\DOMNode $resourceNode)
    {
        $resourceData = [
            'id' => $resourceNode->attributes->getNamedItem('id')->nodeValue,
            'title' => $resourceNode->attributes->getNamedItem('title')->nodeValue,
            'sort_order' => $resourceNode->attributes->getNamedItem('sortOrder')->nodeValue,
            'customer_groups' => [],
            'children' => [],
        ];

        foreach ($resourceNode->childNodes as $child) {
            if ($child->nodeName === 'customer_group') {
                $resourceData['customer_groups'][] = $child->nodeValue;
            }

            if ($child->nodeName === 'resource') {
                $resourceData['children'][] = $this->convertSingleResourceNode($child);
            }
        }

        return $resourceData;
    }

}
