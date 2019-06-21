<?php declare(strict_types=1);

namespace bmorrical\service\discourseClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;

/**
 * Class DiscourseClient
 * @package bmorrical\discourseAPI
 */
class DiscourseClient
{
    /**
     * @var Client $client
     */
    private $client;

    /**
     * DiscourseClient constructor.
     *
     * @param string $host
     * @param bool $secure
     * @param bool $verifySSL
     */
    public function __construct(string $host, bool $secure = true, bool $verifySSL = true)
    {
        $http = $secure ? 'https' : 'http';
        $this->client = new Client([
            'base_uri' => sprintf('%s://%s/', $http, $host),
            'headers' => [],
            'verify' => $verifySSL,
        ]);
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * GET Request
     *
     * @param $uri
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($uri)
    {
        $request = new Request('GET', $uri);
        try {
            $guzzleResponse = $this->client->send($request);
        } catch (\Throwable $exception) {
            throw new BadResponseException($exception->getMessage(), $request);
        }
        return $guzzleResponse->getBody()->getContents();
    }

    /**
     * DELETE Request
     *
     * @param $uri
     * @param $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($uri, $data)
    {
        $request = new Request('DELETE', $uri, [], json_encode($data));
        try {
            $guzzleResponse = $this->client->send($request);
        } catch (\Throwable $exception) {
            throw new BadResponseException($exception->getMessage(), $request);
        }
        return $guzzleResponse->getBody()->getContents();
    }

    /**
     * POST Request
     *
     * @param $uri
     * @param $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($uri, $data)
    {
        $request = new Request('POST', $uri, [], $data);
        try {
            $guzzleResponse = $this->client->send($request);
        } catch (\Throwable $exception) {
            throw new BadResponseException($exception->getMessage(), $request);
        }
        return $guzzleResponse->getBody()->getContents();
    }

    /**
     * PUT Request
     *
     * @param $uri
     * @param $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put($uri, $data = [])
    {
        $request = new Request('PUT', $uri, ['body' => $data]);
        try {
            $guzzleResponse = $this->client->send($request);
        } catch (\Throwable $exception) {
            throw new BadResponseException($exception->getMessage(), $request);
        }
        return $guzzleResponse->getBody()->getContents();
    }
}
