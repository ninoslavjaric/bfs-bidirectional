<?php
namespace Htec\Service;

use Htec\Exception\InvalidParamsException;
use Htec\Exception\NotFoundException;
use Htec\Service;
use Htec\Traits\Service\UserServiceTrait;

class User extends Service
{
    use UserServiceTrait;

    private const CURRENT_USER_KEY = 'currentUser';

    public function getByToken($token)
    {
        return $this->getBy('token', $token);
    }

    public function register($params): void
    {
        $this->create($params);
    }

    public function authenticate(array $params): string
    {
        if (!isset($params['username'], $params['password'])) {
            throw new InvalidParamsException('No username or param sent');
        }

        $username = $params['username'];
        $password = $params['password'];

        $user = $this->getBy('username', $username);

        if (empty($user)) {
            throw new NotFoundException('User not found');
        }

        $password = $this->hashPassword($password, $user['salt']);

        if ($password != $user['password']) {
            throw new InvalidParamsException('User not found');
        }

        $expirationDate = new \DateTime('+30 minutes');
        $user = $this->edit([
            'id' => $user['id'],
            'token' => $this->generateAccessToken($user),
            'tokenExpires' => $expirationDate->format('Y-m-d H:i:s'),
        ]);

        return $user['token'];
    }

    public function isTokenExpired(array $userData): bool
    {
        return new \DateTime($userData['tokenExpires']) < new \DateTime();
    }

    protected function beforeCreate(array &$data): void
    {
        parent::beforeCreate($data);
        $data['salt'] = $this->generateSalt();
        $data['password'] = $this->hashPassword($data['password'], $data['salt']);
    }

    private function generateSalt(): string
    {
        return hash('adler32', microtime());
    }

    private function hashPassword(string $password, string $salt): string
    {
        $password = $salt . $password . $salt;
        return hash('sha256', $password);
    }

    private function generateAccessToken(array $data): string
    {
        return sha1(microtime() . $data['username']);
    }

    public function getCurrentUser(): array
    {
        $data = self::getRequestScopeData();

        return $data[static::CURRENT_USER_KEY] ?? [];
    }

    public function setCurrentUserData($data): void
    {
        self::setRequestScopeData(static::CURRENT_USER_KEY, $data);
    }
}
