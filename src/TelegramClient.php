<?php

namespace Telegram\Bot;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\HttpClients\GuzzleHttpClient;
use Telegram\Bot\HttpClients\HttpClientInterface;

/**
 * Class TelegramClient.
 */
class TelegramClient
{
    /** @var string Telegram Bot API URL. */
    const BASE_BOT_URL = 'https://api.telegram.org/bot';

    /** @var HttpClientInterface|null HTTP Client. */
    protected $httpClientHandler;

    /** @var string|null base bot url. */
    protected $baseBotUrl;

    /**
     * Instantiates a new TelegramClient object.
     *
     * @param  string|null  $baseBotUrl
     */
    public function __construct(HttpClientInterface $httpClientHandler = null, $baseBotUrl = null)
    {
        $this->httpClientHandler = $httpClientHandler ?? new GuzzleHttpClient();

        $this->baseBotUrl = $baseBotUrl;
    }

    /**
     * Returns the HTTP client handler.
     *
     * @return HttpClientInterface
     */
    public function getHttpClientHandler()
    {
        return $this->httpClientHandler;
    }

    /**
     * Sets the HTTP client handler.
     */
    public function setHttpClientHandler(HttpClientInterface $httpClientHandler): self
    {
        $this->httpClientHandler = $httpClientHandler;

        return $this;
    }

    /**
     * Send an API request and process the result.
     *
     *
     * @throws TelegramSDKException
     */
    public function sendRequest(TelegramRequest $request): TelegramResponse
    {
        [$url, $method, $headers, $isAsyncRequest] = $this->prepareRequest($request);

        $options = $this->getOption($request, $method);

        $rawResponse = $this->getHttpClientHandler()
            ->setTimeOut($request->getTimeOut())
            ->setConnectTimeOut($request->getConnectTimeOut())
            ->send(
                $url,
                $method,
                $headers,
                $options,
                $isAsyncRequest
            );

        $returnResponse = $this->getResponse($request, $rawResponse);

        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }

        return $returnResponse;
    }

    /**
     * Prepares the API request for sending to the client handler.
     */
    public function prepareRequest(TelegramRequest $request): array
    {
        $url = $this->getBaseBotUrl().$request->getAccessToken().'/'.$request->getEndpoint();

        return [
            $url,
            $request->getMethod(),
            $request->getHeaders(),
            $request->isAsyncRequest(),
        ];
    }

    /**
     * Returns the base Bot URL.
     */
    public function getBaseBotUrl(): string
    {
        return $this->baseBotUrl ?? static::BASE_BOT_URL;
    }

    /**
     * Creates response object.
     *
     * @param  ResponseInterface|PromiseInterface  $response
     */
    protected function getResponse(TelegramRequest $request, $response): TelegramResponse
    {
        return new TelegramResponse($request, $response);
    }

    /**
     * @param  string  $method
     * @return array
     */
    private function getOption(TelegramRequest $request, $method)
    {
        if ($method === 'POST') {
            return $request->getPostParams();
        }

        return ['query' => $request->getParams()];
    }
}
