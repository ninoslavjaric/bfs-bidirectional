<?php
namespace Htec\Controller;

use Htec\Controller;
use Htec\Core\JsonResponse;
use Htec\Exception\InvalidParamsException;
use Htec\Exception\NotFoundException;
use Htec\Service\City;
use Htec\Service\Comment;
use Htec\Traits\Service\CityServiceTrait;
use Htec\Traits\Service\CommentServiceTrait;

final class CityController extends Controller
{
    use CityServiceTrait;
    use CommentServiceTrait;

    static public function getEndpointAccessScope(): array
    {
        return [
            'index' => self::ACCESS_LEVEL_TOKEN,
            'search' => self::ACCESS_LEVEL_TOKEN,
            'postCreate' => self::ACCESS_LEVEL_ADMIN,
        ];
    }

    public function indexAction(): JsonResponse
    {
        try {
            $cities = $this->getCityService()->getAll();
            return $this->getSuccessResponse('Cities found', $cities);
        } catch (\Exception $e) {
            return $this->getErrorResponse("Cities not found");
        }
    }

    public function searchAction($searchTerm): JsonResponse
    {
        try {
            $cities = $this->getCityService()->searchBy($searchTerm);
            return $this->getSuccessResponse('Cities found', $cities);
        } catch (NotFoundException $e) {
            return $this->getErrorResponse($e->getMessage());
        }catch (\Exception $e) {
            return $this->getErrorResponse("Cities not found");
        }
    }

    public function postCreateAction(): JsonResponse
    {
        try {
            $city = $this->getCityService()->create($this->request->getParams());
            $comment = $this->getCommentService()->create([
                'text' => $this->request->getParam('comment'),
                'cityId' => $city['id']
            ]);
            $city['comments'] = [$comment['text']];
            return $this->getSuccessResponse('City created', $city);
        } catch (InvalidParamsException $e) {
            return $this->getErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->getErrorResponse("City not created");
        }
    }
}
