<?php
declare(strict_types=1);

namespace Magenest\CustomBreadcrumbs\ViewModel\Product;

use Magenest\CustomBreadcrumbs\Model\BreadcrumbCategoryResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Breadcrumbs implements ArgumentInterface
{
    public function __construct(
        private readonly Registry $registry,
        private readonly BreadcrumbCategoryResolver $resolver,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Crumb list for the PDP, or null when breadcrumbs should be hidden.
     *
     * Shape per crumb: ['label' => string, 'title' => string, 'link' => string, 'last' => bool].
     *
     * @return array<int, array{label:string,title:string,link:string,last:bool}>|null
     */
    public function getCrumbs(): ?array
    {
        $product = $this->getCurrentProduct();
        if ($product === null) {
            return null;
        }

        $deepest = $this->resolver->resolve($product);
        if ($deepest === null) {
            return null;
        }

        $crumbs = [];

        $crumbs[] = [
            'label' => (string) __('Home'),
            'title' => (string) __('Go to Home Page'),
            'link'  => $this->storeManager->getStore()->getBaseUrl(),
            'last'  => false,
        ];

        foreach ($this->resolver->getCategoryPath($deepest) as $category) {
            $crumbs[] = [
                'label' => (string) $category->getName(),
                'title' => (string) $category->getName(),
                'link'  => (string) $category->getUrl(),
                'last'  => false,
            ];
        }

        $crumbs[] = [
            'label' => (string) $product->getName(),
            'title' => '',
            'link'  => '',
            'last'  => true,
        ];

        return $crumbs;
    }

    private function getCurrentProduct(): ?ProductInterface
    {
        $product = $this->registry->registry('current_product');
        return $product instanceof ProductInterface ? $product : null;
    }
}
