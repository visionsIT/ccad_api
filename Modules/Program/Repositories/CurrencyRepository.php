<?php namespace Modules\Program\Http\Repositories;

use App\Repositories\Repository;
use Modules\Program\Models\Currency;

class CurrencyRepository extends Repository
{
    protected $modeler = Currency::class;
}
