<?php declare(strict_types=1);

namespace App\Service;

use bmorrical\discourseAPI\DiscourseAPI;
use bmorrical\discourseAPI\DiscourseLegacyAPI;
use Psr\Log\InvalidArgumentException;
use GuzzleHttp\Exception\GuzzleException;

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
     * Add New User Request
     *
     * @param array $filters
     * @return array
     * @throws GuzzleException
     */
    public function addNewUser(array $filters): array
    {
        // Create the user
        $user = $this->createNewUserAction($filters);
        if (!$user['success']) {
            return [
                'success' => false,
                'errors' => ['User was not created successfully.'],
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
     * Suspend User Request
     *
     * @param array $filters
     * @return array
     * @throws GuzzleException
     */
    public function suspendUser(array $filters): array
    {
        $userId = $this->getUserIdByUsernameAction($filters['username']);
        $response = $this->suspendUserByUserIdAction($userId['data']);

        if (!$response['success']) {
            return [
                'success' => false,
                'errors' => ['User was not suspended successfully.'],
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'errors' => [],
            'data' => ['User was suspended successfully'],
        ];
    }

    /**
     * Call from controller for request to add new user
     *
     * @param array $filters
     * @return array
     * @throws GuzzleException
     */
    public function unsuspendUser(array $filters): array
    {
        $userId = $this->getUserIdByUsernameAction($filters['username']);
        $response = $this->unsuspendUserByUserIdAction($userId['data']);

        if (!$response['success']) {
            return [
                'success' => false,
                'errors' => ['User was not unsuspended successfully.'],
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'errors' => [],
            'data' => ['User was unsuspended successfully'],
        ];
    }


/// PRIVATE MEMBERS
///
///

    /**
     * Gets the User Id by Username
     *
     * @param string $username
     * @return array
     * @throws GuzzleException
     */
    private function getUserIdByUsernameAction(string $username): array
    {
        try {
            return $this->api->getUserIdByUsername($username);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not get User Id for username: %s',
                    $username
                )
            );
        }
    }

    /**
     * Create User
     *
     * @param array $filters
     * @return array
     * @throws GuzzleException
     */
    private function createNewUserAction(array $filters = []): array
    {
        try {
            return $this->api->createUser($filters);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not create user for username: %s',
                    $filters['username']
                )
            );
        }
    }

    /**
     * Suspend User by User ID
     *
     * @param int $userId
     * @return array
     * @throws GuzzleException
     */
    private function suspendUserByUserIdAction(int $userId): array
    {
        try {
            return $this->api->suspendUserById($userId);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not suspend user with id: %s',
                    $userId
                )
            );
        }
    }


    /**
     * Unsuspend User by User ID
     *
     * @param int $userId
     * @return array
     * @throws GuzzleException
     */
    private function unsuspendUserByUserIDAction(int $userId): array
    {
        try {
            return $this->api->unsuspendUserById($userId);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                sprintf(
                    'Could not unsuspend user with id: %s',
                    $userId
                )
            );
        }
    }
}
