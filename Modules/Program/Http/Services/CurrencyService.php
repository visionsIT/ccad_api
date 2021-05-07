<?php namespace Modules\Program\Http\Services;

use Modules\Program\Repositories\CurrencyRepository;

class CurrencyService
{
    private $repository;

    public function __construct(CurrencyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->repository->get();
    }

}
