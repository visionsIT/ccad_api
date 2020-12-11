<?php namespace App\Contracts;

interface Repository
{

    /**
     * Repository constructor.
     */
    public function __construct();

    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id);

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function first(array $columns = [ '*' ]);

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function get(array $columns = [ '*' ]);

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data);

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function insert(array $data);

    /**
     * @param $data
     * @param $id
     *
     * @return mixed
     */
    public function update($data, $id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function destroy($id);
}