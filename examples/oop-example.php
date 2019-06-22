<?php declare(strict_types=1);

namespace App\Service;

use bmorrical\discourseAPI\DiscourseAPI;
use bmorrical\discourseAPI\DiscourseLegacyAPI;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class HelpService
 * @package App\Service
 */
class HelpService
{
    /**
     * @var string $apiUrl
     */
    private $apiUrl;

    /**
     * @var string $apiKey
     */
    private $apiKey;

    /**
     * @var DiscourseLegacyAPI $legacyApi
     */
    private $legacyApi;

    /**
     * @var DiscourseAPI $api
     */
    private $api;

    /**
     * HelpService constructor.
     *
     * @param string $apiUrl
     * @param string $apiKey
     */
    public function __construct(string $apiUrl, string $apiKey)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
        $this->legacyApi = new DiscourseLegacyAPI($this->apiUrl, $this->apiKey, 'https');
        $this->api = new DiscourseAPI($this->apiUrl, $this->apiKey);
    }

    /**
     * Call from controller for request to add new user
     *
     * @param array $filters
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addNewUser(array $filters): array
    {
        $result = $this->createUser($filters);
        if (!$result['success']) {
            return [
                'success' => false,
                'errors' => ['User was not created successfully.'],
                'data' => [],
            ];
        }
        $user = $this->getUserIdByUsername($filters);
        if (!$result['success']) {
            return [
                'success' => false,
                'errors' => ['Could not get user by Username.'],
                'data' => [],
            ];
        }
        $result = $this->activateUserById($user['data']->id);
        if (!$result['success']) {
            return [
                'success' => false,
                'errors' => ['Could not get user by Username.'],
                'data' => [],
            ];
        }
        $result = $this->approveUserById($user['data']->id);
        if (!$result['success']) {
            return [
                'success' => false,
                'errors' => ['Could not get user by Username.'],
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'errors' => [],
            'data' => ['User was created, activated, and approved successfully'],
        ];
    }

    /**
     * Create User
     *
     * @param array $filters
     * @return array
     */
    private function createUser(array $filters = []): array
    {
        try {
            // create user
            $response = $this->legacyApi->createUser(
                $filters['name'],
                $filters['username'],
                $filters['email'],
                $filters['password']
            );
        } catch (NotFoundHttpException $exception) {
            throw new NotFoundHttpException('Could not create user');
        }

        if (!$response->apiresult->success) {
            throw new NotFoundHttpException(sprintf(
                'User was not created successfully: %s',
                $response->apiresult->message
            ));
        } else {
            return [
                'success' => true,
                'error' => [],
                'data' => $response->apiresult->message
            ];
        }
    }

    /**
     * Get a User Username
     *
     * @param array $filters
     * @return array
     */
    private function getUserIdByUsername(array $filters = []): array
    {
        try {
            // get user
            $response = $this->legacyApi->getUserByUsername(
                $filters['username']
            );
        } catch (NotFoundHttpException $exception) {
            throw new NotFoundHttpException('Could not create user');
        }

        if (200 !== $response->http_code) {
            throw new NotFoundHttpException(
                sprintf(
                    'User was not found successfully: %s',
                    $response->apiresult->errors
                )
            );
        } else {
            return [
                'success' => true,
                'error' => [],
                'data' => $response->apiresult->user
            ];
        }
    }

    /**
     * Activate a user by ID
     *
     * @param int $user_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function activateUserById(int $user_id): array
    {
        try {
            return $this->api->activateUserById($user_id);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException('Could not activate user');
        }
    }

    /**
     * Approve a user by ID
     *
     * @param int $user_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function approveUserById(int $user_id): array
    {
        try {
            return $this->api->approveUserById($user_id);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException('Could not approve user');
        }
    }
}
