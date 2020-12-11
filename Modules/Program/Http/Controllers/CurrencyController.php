<?php namespace Modules\Program\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Program\Http\Services\CurrencyService;
use Modules\Program\Transformers\CurrenciesTransformer;
use Spatie\Fractal\Fractal;

class CurrencyController extends Controller
{
    private $service;

    public function __construct(CurrencyService $service)
    {
        $this->service = $service;
    }


    /**
     *
     * @return Fractal
     */
    public function index(): Fractal
    {
        $currencies = $this->service->get();

        return fractal($currencies, new CurrenciesTransformer());
    }

}
