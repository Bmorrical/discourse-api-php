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
     * Create a new user
     *
     * @param array $filters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser(array $filters): array
    {
        $user = $this->getUserIdByUsername($filters['username']);

        // Does user already exist?
        if ($user['success']) {
            // Try to unsuspend the user
            $response = $this->unsuspendUserByID($user['data']);

            if ($response['success']) {
                return [
                    'success' => true,
                    'errors' => [],
                    'data' => [
                        'user_active' => true,
                        'user_id' => $user['data'],
                        'message' => 'User was sucessfully unsuspended via account creation.',
                    ],
                ];
            } else {
                return [
                    'success' => true,
                    'errors' => ['Something went wrong, user was found but could not be unsuspended.'],
                    'data' => [],
                ];
            }
        } else {
            $honeypot = $this->getHoneypot();

            if ($honeypot['success']) {
                $query = [
                    'name' => $filters['name'],
                    'username' => $filters['username'],
                    'email' => $filters['email'],
                    'password' => $filters['password'],

                    // activate and approve the user
                    'active' => true,
                    'approved' => true,

                    // honepot
                    'challenge' => strrev($honeypot['data']->challenge),
                    'password_confirmation' => $honeypot['data']->value,

                    // api
                    'api_key' => $this->apiKey,
                    'api_username' => $this->apiUsername,
                ];

                try {
                    $response = $this->client->post('/users', http_build_query($query));
                } catch (\Throwable $exception) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Could not create user with username: %s. Error Message: %s',
                            $filters['username'],
                            $exception->getMessage()
                        )
                    );
                }

                $result = json_decode($response);
            }
            if ($result->success) {
                return [
                    'success' => true,
                    'errors' => [],
                    'data' => [
                        'user_active' => $result->active,
                        'user_id' => $result->user_id,
                        'message' => $result->message,
                    ],
                ];
            }
        }

        return [
            'success' => false,
            'errors' => ['Something went wrong, could not create the user'],
            'data' => [],
        ];

    }

    /**
     * Activate a User by ID
     *
     * @param int $userId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function activateUserById(int $userId): array
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
    public function approveUserById(int $userId): array
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

    /**
     * Get User Id By Username
     *
     * @param string $username
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserIdByUsername(string $username): array
    {
        $query = [
            'api_key' => $this->apiKey,
            'api_username' => $this->apiUsername,
        ];
        $uri = sprintf('/users/%s.json?%s', $username, http_build_query($query));

        try {
            $response = json_decode($this->client->get($uri));
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'errors' => [sprintf('User does not exist for username: %s', $username)],
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'errors' => [],
            'data' => $response->user->id,
        ];
    }

    /**
     * Suspend A User By ID
     *
     * @param int $userId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function suspendUserByID(int $userId): array
    {
        $query = [
            'api_key' => $this->apiKey,
            'api_username' => $this->apiUsername,
            'suspend_until' => "3017-12-12",
            "reason" => "suspended by system"
        ];
        $uri = sprintf('/admin/users/%s/suspend?%s', $userId, http_build_query($query));

        try {
            $response = json_decode($this->client->put($uri));
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not suspend user by user id: %s. Error Message: %s',
                    $userId,
                    $exception->getMessage()
                )
            );
        }

        return [
            'success' => true,
            'errors' => [],
            'data' => [],
        ];
    }

    /**
     * Unsuspend A User By ID
     *
     * @param int $userId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unsuspendUserByID(int $userId): array
    {
        $query = [
            'api_key' => $this->apiKey,
            'api_username' => $this->apiUsername,
        ];
        $uri = sprintf('/admin/users/%s/unsuspend?%s', $userId, http_build_query($query));

        try {
            $response = json_decode($this->client->put($uri));
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not unsuspend user by user id: %s. Error Message: %s',
                    $userId,
                    $exception->getMessage()
                )
            );
        }

        return [
            'success' => true,
            'errors' => [],
            'data' => [],
        ];
    }

    /**
     * Get the Latest Topics
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLatestTopics(): array
    {
        $query = [
            'order' => 'created',
        ];
        $uri = sprintf('latest.json?%s', http_build_query($query));

        try {
            $response = json_decode($this->client->get($uri));
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not get latest topics. Error Message: %s',
                    $exception->getMessage()
                )
            );
        }

        return [
            'success' => true,
            'errors' => [],
            'data' => $response->topic_list->topics,
        ];
    }

///////////////////////
/////// PRIVATE MEMBERS
///////////////////////

    /**
     * Gets a unique value and challenge for purpose of validating some requests via honeypot
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getHoneypot(): array
    {
        try {
            $response = $this->client->get('/users/hp.json');
        } catch (\Throwable $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not get honeypot.  Error Message: %s',
                    $exception->getMessage()
                )
            );
        }

        return [
            'success' => true,
            'data' => json_decode($response),
            'errors' => [],
        ];
    }
}
