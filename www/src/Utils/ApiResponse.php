<?php

namespace App\Utils;

use App\Traits\ArrayAccess;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Helper\FormHelper;
use App\Helper\ObjectHelper;
use App\Interfaces\CoreEntityInterface;

class ApiResponse implements \ArrayAccess
{
    use ArrayAccess;

    /** @var bool $isValid */
    public $code;
    /** @var array $errors */
    public $message;
    /** @var array $embedded */
    public $embedded;

    const SUCCESS = Response::HTTP_OK;
    const EMPTY = Response::HTTP_NO_CONTENT;
    const ERROR = Response::HTTP_BAD_REQUEST;
    const UNAUTHORIZED = Response::HTTP_UNAUTHORIZED;
    const FORBIDDEN = Response::HTTP_FORBIDDEN;
    const CONTINUE = Response::HTTP_CONTINUE;

    public function __construct(int $code, array $embedded = [], string $message = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->embedded = $embedded;
    }

    public function send(int $loggedUserId = null): JsonResponse
    {
        $embedded = [];
        foreach ($this->embedded as $key => $data) {
            if ($this->code === 400 && $data instanceof Form) {
                $embedded['errors'] = FormHelper::getErrors($data);
            } elseif (method_exists($data, 'toArray')) {
                $embedded[$key] = $data->toArray($loggedUserId);
            } else {
                $embedded[$key] = $data;
            }
        }

        return new JsonResponse($embedded, $this->code);
    }

    public static function checkConnected(int $loggedUserId): ApiResponse
    {
        if (!$loggedUserId) {
            return self::returnUnauthorizedResponse();
        }
        return new ApiResponse(ApiResponse::CONTINUE);
    }

    public static function checkValid(Form &$form, array $formData = null): ApiResponse
    {
        if (count($formData) === 0) {
            return ApiResponse::returnEmptyFormResponse($form);
        }

        $form->submit($formData);
        if (!$form->isValid()) {
            return self::returnNotValidFormResponse($form);
        }

        return new ApiResponse(ApiResponse::CONTINUE, ['form' => $form]);
    }

    public static function checkConnectedAndValid(int $loggedUserId, Form &$form, array $formData = null): ApiResponse
    {
        $apiResponseVerification = self::checkConnected($loggedUserId);
        if ($apiResponseVerification->code !== self::CONTINUE) {
            return $apiResponseVerification;
        }

        return self::checkValid($form, $formData);
    }

    public static function returnValidEntityResponse(CoreEntityInterface $entity): ApiResponse
    {
        $className = ObjectHelper::getClassName($entity);

        return new ApiResponse(self::SUCCESS, [$className => $entity]);
    }

    public static function returnValidDataResponse(array $responseData): ApiResponse
    {
        return new ApiResponse(self::SUCCESS, $responseData);
    }

    public static function returnEmptyFormResponse(Form $form): ApiResponse
    {
        return new ApiResponse(self::EMPTY, ['form' => $form]);
    }

    public static function returnEmptyResponse(): ApiResponse
    {
        return new ApiResponse(self::EMPTY);
    }

    public static function returnNotValidFormResponse(Form $form, array $errors = []): ApiResponse
    {
        if (count($errors) === 0) {
            $errors = FormHelper::getErrors($form);
        }

        return new ApiResponse(self::ERROR, ['form' => $form, 'errors' => $errors]);
    }

    public static function returnErrorsResponse(array $errors): ApiResponse
    {
        return new ApiResponse(self::ERROR, ['errors' => $errors]);
    }

    public static function returnUnauthorizedResponse(): ApiResponse
    {
        return new ApiResponse(self::UNAUTHORIZED);
    }

    public static function returnForbiddenResponse(): ApiResponse
    {
        return new ApiResponse(self::FORBIDDEN);
    }
}
