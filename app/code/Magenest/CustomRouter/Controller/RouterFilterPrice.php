<?php
declare(strict_types=1);

namespace Magenest\CustomRouter\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Catalog\Model\CategoryFactory;

/**
 * Class RouterFilterPrice
 */
class RouterFilterPrice implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * RouterFilterPrice constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ResponseInterface $response
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        CategoryFactory $categoryFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @param RequestInterface $request
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        $identifier = trim($request->getPathInfo(), '/');

        if (preg_match('#^(.*)-price-([0-9]+)-([0-9]+)\.html$#', $identifier, $matches)) {
            $categoryUrlKey = $matches[1];
            $min = $matches[2];
            $max = $matches[3];

            $category = $this->categoryFactory->create()
                ->getCollection()
                ->addAttributeToSelect('url_key')
                ->addAttributeToFilter('url_key', $categoryUrlKey)
                ->addAttributeToFilter('is_active', 1)
                ->getFirstItem();

            if ($category && $category->getId()) {
                $request->setModuleName('catalog')
                    ->setControllerName('category')
                    ->setActionName('view')
                    ->setParam('id', $category->getId())
                    ->setParam('price', "{$min}-{$max}");

                return $this->actionFactory->create(Forward::class, ['request' => $request]);
            }
        }

        return null;
    }
}
