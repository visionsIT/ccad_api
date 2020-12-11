<?php namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $exception
     *
     * @return mixed|void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param Exception $e
     *
     * @return \Illuminate\Http\JsonResponse|Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if (env('APP_DEBUG')) {
            return parent::render($request, $e);
        }

        $status = Response::HTTP_INTERNAL_SERVER_ERROR;

        //Illuminate\Database\Eloquent\ModelNotFoundException
        if ($e instanceof HttpResponseException) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        } elseif ($e instanceof ThrottleRequestsException) {
            $status = Response::HTTP_TOO_MANY_REQUESTS;
            $e      = new ThrottleRequestsException(__('exceptions.HTTP_TOO_MANY_REQUESTS', [ 'seconds' => $e->getHeaders()['Retry-After'] ]));
        } elseif ($e instanceof TooManyRequestsHttpException) {
            $status = Response::HTTP_TOO_MANY_REQUESTS;
            $e      = new TooManyRequestsHttpException(NULL, 'HTTP_TOO_MANY_REQUESTS');
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $status = Response::HTTP_METHOD_NOT_ALLOWED;
            $e      = new MethodNotAllowedHttpException([], 'HTTP_METHOD_NOT_ALLOWED', $e);
        } elseif ($e instanceof UnauthorizedException) {
            $status = Response::HTTP_UNAUTHORIZED;
            $e      = new UnauthorizedException('HTTP_UNAUTHORIZED');
        } elseif ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
            $status = Response::HTTP_NOT_FOUND;
            $e      = new NotFoundHttpException('HTTP_NOT_FOUND', $e);
        } elseif ($e instanceof AuthorizationException) {
            $status = Response::HTTP_FORBIDDEN;
            $e      = new AuthorizationException('HTTP_FORBIDDEN', $status);
        } elseif ($e instanceof ValidationException) {
            return response()->json([
                'message' => __('exceptions.INVALID_GIVEN_DATA'),
                'errors'  => $e->errors()
            ], $e->status);
        } elseif ($e) {
            $e = new HttpException($status, 'HTTP_INTERNAL_SERVER_ERROR');
        }

        return response()->json([
            'message' => $e->getMessage()
        ], $status);
    }
}
