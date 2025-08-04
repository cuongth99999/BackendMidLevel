<?php
declare(strict_types=1);

namespace Magenest\RequestLogger\Controller\Log;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magenest\RequestLogger\Model\RequestLogger;

class Index implements HttpGetActionInterface
{
    private RequestLogger $requestLogger;
    private RequestInterface $request;
    private JsonFactory $jsonFactory;

    public function __construct(
        RequestLogger $requestLogger,
        RequestInterface $request,
        JsonFactory $jsonFactory
    ) {
        $this->requestLogger = $requestLogger;
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $clientIp = $this->request->getClientIp();
        $params = $this->request->getParams();

        $this->requestLogger->logRequest($clientIp, $params);

        $result = $this->jsonFactory->create();
        return $result->setData([
            'success' => true,
            'message' => 'Request logged successfully'
        ]);
    }
}