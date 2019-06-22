<?php declare(strict_types=1);

namespace bmorrical\discourseAPI;

use bmorrical\service\discourseClient\DiscourseClient;
use \Psr\Log\InvalidArgumentException;

/**
 * Class DiscourseAPI
 * @package bmorrical\discourseAPI
 */
class DiscourseAPI
{
    /**
     * @var DiscourseClient $client
     */
    private $client;

    /**
     * @var string $apiKey
     */
    private $apiKey;

    /**
     * @var string $apiUsername
     */
    private $apiUsername;

    /**
     * DiscourseAPI constructor.
     *
     * @param string $apiKey
     * @param string $apiHost
     * @param string $apiUsername
     */
    public function __construct(string $apiHost, string $apiKey, string $apiUsername = 'system')
    {
        $this->apiKey = $apiKey;
        $this->apiUsername = $apiUsername;
        $this->client = new DiscourseClient($apiHost);
    }

    /**
     * Activate a User by ID
     *
     * @param int $userId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function activateUserById(int $userId)
    {
        $query = [
            'api_key' => $this->apiKey,
            'api_username' => $this->apiUsername,
        ];
        $uri = sprintf('/admin/users/%s/activate?%s', $userId, http_build_query($query));

        try {
            $response = $this->client->put($uri);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not approve user id: %s. Error Message: %s',
                    $userId,
                    $exception->getMessage()
                )
            );
        }

        return [
            'success' => true,
            'errors' => [],
            'data' => $response,
        ];
    }

    /**
     * Approve a User by ID
     *
     * @param int $userId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function approveUserById(int $userId): array
    {
        $query = [
            'api_key' => $this->apiKey,
            'api_username' => $this->apiUsername,
        ];
        $uri = sprintf('/admin/users/%s/approve?%s', $userId, http_build_query($query));

        try {
            $response = $this->client->put($uri);
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not approve user id: %s. Error Message: %s',
                    $userId,
                    $exception->getMessage()
                )
            );
        }

        return [
            'success' => true,
            'errors' => [],
            'data' => $response,
        ];
    }
}
