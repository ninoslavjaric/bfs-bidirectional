<?php
namespace Htec\Controller;

use Htec\Controller;
use Htec\Core\JsonResponse;
use Htec\Exception\InvalidParamsException;
use Htec\Service\Comment;

final class CommentController extends Controller
{
    static public function getEndpointAccessScope(): array
    {
        return [
            'postCreate' => self::ACCESS_LEVEL_TOKEN,
            'postUpdate' => self::ACCESS_LEVEL_TOKEN,
            'deleteDelete' => self::ACCESS_LEVEL_TOKEN,
        ];
    }

    public function postCreateAction(): JsonResponse
    {
        try {
            $commentData = Comment::getInstance()->create($this->request->getParams());
            return $this->getSuccessResponse("Comment created ", $commentData);
        } catch (\Exception $e) {
            return $this->getErrorResponse("Import not successful");
        }
    }

    public function postUpdateAction(): JsonResponse
    {
        try {
            $commentData = Comment::getInstance()->edit($this->request->getParams());
            return $this->getSuccessResponse("Comment created ", $commentData);
        } catch (InvalidParamsException $e) {
            return $this->getErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->getErrorResponse("Something's wrong");
        }
    }

    public function deleteDeleteAction(): JsonResponse
    {
        try {
            Comment::getInstance()->delete($this->request->getParam('id'));
            return $this->getSuccessResponse("Comment deleted ");
        } catch (InvalidParamsException $e) {
            return $this->getErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->getErrorResponse("Something's wrong");
        }
    }
}
