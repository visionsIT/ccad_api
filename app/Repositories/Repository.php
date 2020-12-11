<?php namespace App\Repositories;

use App\Contracts\Repository as RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class Repository implements RepositoryInterface
{

    /**
     * @var
     */
    protected $object, $modeler;

    /**
     * CrudableRepository constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        if (!$this->modeler or !class_exists($this->modeler)) {
            throw new \Exception('Please set the $modeler property to your repository path.');
        }

        $this->modeler = new $this->modeler;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        if ($this->object && $this->object->id === $id) {
            return $this->object;
        }

        return $this->object = $this->modeler->findOrfail($id);
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function first(array $columns = [ '*' ])
    {
        return $this->modeler->first($columns);
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function select(array $columns = [ '*' ])
    {
        return $this->modeler->select($columns);
    }

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function get(array $columns = [ '*' ])
    {
        return $this->modeler->get($columns);
    }

    public function paginate($pagination_count = 20)
    {
        return $this->modeler->paginate($pagination_count);
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->modeler->create($data);
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function insert(array $data)
    {
        return $this->modeler->insert($data);
    }

    /**
     * @param $data
     * @param $identifier
     *
     * @return mixed|void
     */
    public function update($data, $identifier)
    {
        $object = ($identifier instanceof Model) ? $identifier : $this->find($identifier);

        $object->update($data);
    }

    /**
     * @param $id
     *
     * @return mixed|void
     */
    public function destroy($id)
    {
        $object = $this->find($id);

        $object->destroy($id);
    }
}