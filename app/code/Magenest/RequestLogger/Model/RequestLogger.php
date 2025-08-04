<?php
declare(strict_types=1);

namespace Magenest\RequestLogger\Model;

use Psr\Log\LoggerInterface;

class RequestLogger
{
    private const LOG_FILE = 'request.log';
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log request information
     *
     * @param string $clientIp
     * @param array $params
     * @return void
     */
    public function logRequest(string $clientIp, array $params): void
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'client_ip' => $clientIp,
            'parameters' => $params
        ];

        $this->logger->info(
            'Request logged',
            $logData
        );
    }
}