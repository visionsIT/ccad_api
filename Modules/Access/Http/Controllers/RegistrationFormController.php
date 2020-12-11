<?php

namespace Modules\Access\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Access\Transformers\RegistrationFormTransformer;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Program\Models\Program;
use Modules\Access\Http\Requests\RegistrationFormRequest;
use Modules\Access\Transformers\AccessTypeTransformer;
use Modules\Access\Http\Services\RegistrationFormService;

class RegistrationFormController extends Controller
{
    private $service;

    /**
     * AccessTypeController constructor.
     * @param RegistrationFormService $service
     */
    public function __construct(RegistrationFormService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Program $program
     *
     * @return Fractal
     */
    public function show(Program $program): Fractal
    {
        $form = $this->service->getProgramRegistrationForm($program);

        return fractal($form, new RegistrationFormTransformer());
    }


    /**
     * @param Request $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $this->service->update($request->all(), $id);

        return response()->json([ 'message' => 'Registration Form Updated Successfully' ]);
    }

}
