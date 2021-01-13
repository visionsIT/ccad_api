<?php namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use \Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\User\Models\Departments;
use Modules\User\Transformers\DepartmentsTransformer;

class DepartmentController extends Controller
{
    private $departments;

    public function __construct(Departments $departments)
    {
        $this->departments = $departments;
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $departments = $this->departments->get();

        return fractal($departments, new DepartmentsTransformer());
    }
}
