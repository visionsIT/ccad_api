<?php namespace Modules\Agency\Http\Controllers;

use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Agency\Http\Requests\CatalogueRequest;
use Modules\Agency\Transformers\CatalogueTransformer;
use Modules\Agency\Repositories\CatalogueRepository;

class CatalogueController extends Controller
{
    private $repository;

    public function __construct(CatalogueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $catalogues = $this->repository->get();

        return fractal($catalogues, new CatalogueTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CatalogueRequest $request
     * @return Fractal
     */
    public function store(CatalogueRequest $request)
    {
        $catalogue = $this->repository->create($request->all());

        return fractal($catalogue, new CatalogueTransformer);
    }

    /**
     * Show the specified resource.
     *
     * @param $id
     *
     * @return Fractal
     */
    public function show($id): Fractal
    {
        $catalogue = $this->repository->find($id);

        return fractal($catalogue, new CatalogueTransformer);
    }

    /**
     *
     * Update the specified resource in storage.
     *
     * @param CatalogueRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(CatalogueRequest $request, $id): JsonResponse
    {
        $this->repository->update($request->all(), $id);

        return response()->json(['message' => 'Catalogue Updated Successfully']);
    }

    /**
     *
     *  Remove the specified resource from storage.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->repository->destroy($id);

        return response()->json(['message' => 'Catalogue Trashed Successfully']);
    }
}
