<?php
namespace Htec\Traits\Service;

use Htec\Service\Comment;

trait CommentServiceTrait
{
    private Comment $commentService;

    /**
     * @return Comment
     */
    public function getCommentService(): Comment
    {
        if (!isset($this->commentService)) {
            $this->setCommentService(new Comment());
        }

        return $this->commentService;
    }

    /**
     * @param Comment $commentService
     */
    public function setCommentService(Comment $commentService): void
    {
        $this->commentService = $commentService;
    }

}
