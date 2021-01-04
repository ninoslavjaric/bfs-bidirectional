<?php

namespace TestHtec\Service;

use Htec\Exception\InvalidParamsException;
use Htec\Exception\NotFoundException;
use Htec\Service\User;
use Htec\Mapper\User as UserMapper;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $userService;
    private UserMapper $userMapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = User::getInstance();

        $this->userMapper = $this->createMock(UserMapper::class);

        $this->userService->setMapper($this->userMapper);
    }

    public function authenticationMissingParams(): array
    {
        return [
            [['username' => null, 'password' => null]],
            [['username' => null]],
            [['password' => null]],
        ];
    }

    /**
     * @param $username
     * @param $password
     * @dataProvider authenticationMissingParams
     */
    public function testAuthenticationWithMissingParams($params): void
    {
        $this->expectException(InvalidParamsException::class);
        $this->userMapper->expects($this->never())->method('findRowByWhere');
        $this->userMapper->expects($this->never())->method('getColumnsDefinition');
        $this->userMapper->expects($this->never())->method('update');
        $this->userService->authenticate($params);
    }

    public function testAuthenticationWithCorrectParamsWhenThereIsNoUserFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('User not found');
        $this->userMapper->expects($this->once())->method('findRowByWhere')->willReturn([]);
        $this->userMapper->expects($this->exactly(2))->method('getColumnsDefinition')->willReturn([
            ['column' => 'username', 'name' => 'username']
        ]);
        $this->userMapper->expects($this->never())->method('update');
        $this->userService->authenticate(['username' => 'user', 'password'=>'password']);
    }

    private function getUserData(): array
    {
        return [
            'id' => '3',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'username' => 'john.doe',
            'password' => 'd82a363c9b49f030fced655ff8f6ded2a4618dfa6b618083a385ad2224da17a8',
            'salt' => '2cde0425',
            'role' => 'user',
            'token' => '',
            'tokenExpires' => '',
        ];
    }

    private function getUserDataWithToken(): array
    {
        $userData = $this->getUserData();
        $userData['token'] = '123';
        return $userData;
    }

    public function testAuthenticationWithCorrectParamsWrongPasswordWhenThereIsUserFound(): void
    {
        $this->expectException(InvalidParamsException::class);
        $this->expectExceptionMessage('User not found');

        $this->userMapper->expects($this->once())->method('findRowByWhere')->willReturn($this->getUserData());

        $userMapper = new UserMapper();
        $this->userMapper->expects($this->exactly(2))->method('getColumnsDefinition')->willReturn(
            $userMapper->getColumnsDefinition()
        );
        $this->userMapper->expects($this->never())->method('update');

        $this->userService->authenticate(['username' => 'john.doe', 'password'=>'wrong']);
    }

    public function testAuthenticationWithCorrectParamsWhenThereIsUserFound(): void
    {
        $this->userMapper->expects($this->once())->method('findRowByWhere')->willReturn($this->getUserData());

        $userMapper = new UserMapper();
        $this->userMapper->expects($this->exactly(3))->method('getColumnsDefinition')->willReturn(
            $userMapper->getColumnsDefinition()
        );
        $this->userMapper->expects($this->once())->method('update');

        $this->userMapper->expects($this->once())->method('findRowBy')->willReturn($this->getUserDataWithToken());

        $token = $this->userService->authenticate(['username' => 'john.doe', 'password'=>'true']);

        $this->assertNotEmpty($token);
        $this->assertEquals('123', $token);
    }

}
