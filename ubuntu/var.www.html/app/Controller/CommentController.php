<?php
namespace Htec\Controller;

use Htec\Controller;
use Htec\Core\JsonResponse;
use Htec\Exception\InvalidParamsException;
use Htec\Service\Comment;
use Htec\Traits\Service\CommentServiceTrait;

final class CommentController extends Controller
{
    use CommentServiceTrait;

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
            $commentData = $this->getCommentService()->create($this->request->getParams());
            return $this->getSuccessResponse("Comment created ", $commentData);
        } catch (\Exception $e) {
            return $this->getErrorResponse("Comment not created");
        }
    }

    public function postUpdateAction(): JsonResponse
    {
        try {
            $commentData = $this->getCommentService()->edit($this->request->getParams());
            return $this->getSuccessResponse("Comment updated ", $commentData);
        } catch (InvalidParamsException $e) {
            return $this->getErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->getErrorResponse("Something's wrong");
        }
    }

    public function deleteDeleteAction(): JsonResponse
    {
        try {
            $this->getCommentService()->delete($this->request->getParam('id'));
            return $this->getSuccessResponse("Comment deleted ");
        } catch (InvalidParamsException $e) {
            return $this->getErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->getErrorResponse("Something's wrong");
        }
    }
}
