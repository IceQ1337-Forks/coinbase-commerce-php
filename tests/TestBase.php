<?php

namespace CoinbaseCommerce\Tests;

use CoinbaseCommerce\ApiClient;
use CoinbaseCommerce\Tests\GuzzleClientMock\GuzzleMockClientFactoryMethod;
use PHPUnit\Framework\TestCase;

abstract class TestBase extends TestCase
{
    protected $apiClient;

    protected $httpClientWrapper;

    protected $logger;

    public function setUp(): void
    {
        $this->initMockClient();

        parent::setUp();
    }

    public function initMockClient()
    {
        $this->httpClientWrapper = GuzzleMockClientFactoryMethod::create();
        $client = $this->httpClientWrapper->getClient();

        $this->apiClient = $this->getMockBuilder(ApiClient::getClassName())
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods(['getHttpClient', 'init', 'setLogger'])
            ->getMock();

        $this->apiClient
            ->method('getHttpClient')
            ->willReturn($client);

        // https://github.com/sebastianbergmann/phpunit/issues/4852
        $this->logger = $this->getMockBuilder('LoggerClass')
            ->setMethods(['warning'])
            ->getMock();

        $this->logger
            ->method('warning')
            ->willReturnArgument(0);

        $this->apiClient->method('init')->with('test_key');
        $this->apiClient->method('setLogger')->with($this->logger);
    }

    /**
     * @param $statusCode
     * @param array $body
     * @param array $headers
     */
    public function appendRequest($statusCode, $body, $headers = [])
    {
        $this->httpClientWrapper->appendRequest($statusCode, $body, $headers);
    }

    /**
     * @param string $file
     * @return mixed
     * @throws \Exception
     */
    public function parseJsonFile($file)
    {
        $filePath = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixtures' . \DIRECTORY_SEPARATOR . $file;

        if (!\file_exists($filePath)) {
            throw new \Exception('File not exists');
        }

        $data = \file_get_contents($filePath);

        return \json_decode($data, true);
    }

    public function assertRequested($method, $path, $params = '')
    {
        $request = $this->httpClientWrapper->shiftTransactionRequest();

        $this->assertEquals($method, $request['method']);
        $this->assertEquals($path, $request['path']);
        $this->assertEquals($params, $request['params']);
    }
}
