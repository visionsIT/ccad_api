<?php namespace Modules\User\Http\Services;

use Illuminate\Validation\ValidationException;
use Modules\User\Http\Repositories\PointRepository;
use Modules\User\Http\Repositories\UserRepository;

class PointService
{
    private $repository, $user_service, $user_repository;

    public function __construct(PointRepository $repository, UserService $user_service, UserRepository $userRepository)
    {
        $this->repository      = $repository;
        $this->user_service    = $user_service;
        $this->user_repository = $userRepository;
    }


    /**
     * @param $pagination_count
     * @param $proram_id
     * @param array $data
     *
     * @return mixed
     */
    public function get($pagination_count, $proram_id, $data = [])
    {
//        return $data ? $this->repository->filter($data, $pagination_count) : $this->repository->paginate($pagination_count);
        return $data ? $this->repository->filter($data, $pagination_count) : $this->repository->get();
    }

    /**
     * @param $user
     * @param $data
     * @param string $op
     *
     * @return mixed
     */
    public function store($user, $data, $op = '+')
    {
        $current_balance = $this->currentUserBalance($user);

        // throw an exception if the deduction value is greater than the current balance of points
//        if ($data['value'] > $current_balance) {
//            throw ValidationException::withMessages([ 'value' => 'This account does not have enough points to move this amount' ]);
//        }
        if(isset($data['created_by_id']) && $data['created_by_id'] != ''){

        } else {
            $data['created_by_id'] = \Auth::id();
        }

        $data['user_id']             = $user->id;
        $data['balance']             = $op === '-' ? ($current_balance - $data['value']) : ($current_balance + $data['value']);
        $data['transaction_type_id'] = 6; // place an order

        if ($op === '-') {
            $data['balance'] = $current_balance - $data['value'];
            $data['value']   = -1 * $data['value'];
        } else {
            $data['balance'] = $current_balance + $data['value'];
        }

        return $this->repository->create($data);
    }

    /**
     * @param $user
     * @param $data
     *
     * @return mixed
     */
    public function addPointsToSpecificUser($user, $data, $attachmentUrl = '')
    {
        $current_balance = $this->currentUserBalance($user);

        $data['created_by_id']       = \Auth::id();
        $data['user_id']             = $user->id;
        $data['balance']             = $current_balance + $data['value'];
        $data['transaction_type_id'] = 7; // Add direct points
        $data['attachment'] = $attachmentUrl;

        return $this->repository->create($data);
    }


    /**
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param $program
     *
     * @return mixed
     */
    public function currentBalance($program)
    {
        return $this->repository->aggregateBalance($program->id);
    }

    /**
     * @param $user
     *
     * @return mixed
     */
    public function currentUserBalance($user)
    {
        return $this->repository->aggregateUserBalance($user->id);
    }

    public function filterPoints($data)
    {
        return $this->repository->filterPointsHistory($data);
    }

}
