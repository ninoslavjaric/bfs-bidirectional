<?php
namespace Htec\Service;

use Htec\Core\Request;
use Htec\Exception\InvalidParamsException;
use Htec\Service;

final class Comment extends Service
{
    private function getUserId()
    {
        $user = User::getInstance()->getByToken(Request::getInstance()->getToken());

        return $user['id'];
    }
    protected function beforeCreate(array &$data): void
    {
        $data['userId'] = $this->getUserId();

        parent::beforeCreate($data);
    }

    private function validateOwnership(int $id): void
    {
        $comment = $this->getBy('id', $id);

        if ($comment['userId'] != $this->getUserId()) {
            throw new InvalidParamsException("Comment is not owned by you.");
        }
    }

    protected function beforeEdit(array &$data)
    {
        parent::beforeEdit($data);
        $this->validateOwnership($data['id']);
    }

    protected function beforeDelete(int $id)
    {
        parent::beforeDelete($id);
        $this->validateOwnership($id);
    }
}
