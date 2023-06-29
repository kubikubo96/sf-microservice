<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $_model;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->setModel();
    }

    /**
     * Get model
     *
     * @return mixed
     */
    abstract public function getModel();

    /**
     * Set model
     */
    public function setModel()
    {
        $this->_model = resolve($this->getModel());
    }

    /**
     * Get all
     */
    public function all()
    {
        return $this->_model->all();
    }

    /**
     * Get one
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->_model->find($id);
    }

    /**
     * Create
     *
     * @param array $data
     * @param string $primary_key
     * @return mixed
     */
    public function create(array $data, $primary_key = 'id')
    {
        $result = $this->_model->create($data);
        try {
            if ($result) {
                return $this->_model->find($result[$primary_key]);
            }
        } catch (\Exception $e) {
            return $result;
        }
        return $result;
    }

    /**
     * Insert
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return $this->_model->insert($data);
    }

    /**
     * Update
     * @param $id
     * @param array $data
     * @return bool|mixed
     */
    public function update($id, array $data)
    {
        $result = $this->find($id);
        if ($result) {
            $result->update($data);
            return $result;
        }
        return false;
    }

    /**
     * Delete
     *
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        $result = $this->find($id);
        if ($result) {
            $result->delete();
            return true;
        }
        return false;
    }

    /**
     * Xóa không cần kiểm tra tồn tại
     * $ids example = (1),(1,2,3),[1, 2, 3],collect([1, 2, 3]
     * @param $ids
     * @return int
     */
    public function destroy($ids)
    {
        return $this->_model->destroy($ids);
    }

    /**
     * Create Or Update
     * @param array $data
     * @return mixed
     */
    public function createOrUpdate(array $data)
    {
        if (isset($data['update_id'])) {
            $this->update($data['update_id'], $data);
        }
        return $this->create($data);
    }

    /**
     * Update Or Create
     *
     * Nếu tìm được column theo option, thì update data
     * Nếu không tìm được, thì tạo mới theo option và data
     *
     * @param array $option
     * @param array $data
     * @return mixed
     */
    public function updateOrCreate(array $option, array $data)
    {
        return $this->_model->updateOrCreate($option, $data);
    }

    /**
     * Query data
     *
     * @param array $options
     * @param array $with
     * @param array $order
     */
    public function query($options = [], $with = [], $order = [])
    {
        $query = $this->_model;
        if ($options) {
            $query = $this->queryOptions($options, $query);
        }
        if ($with) {
            $query = $query->with($with);
        }
        return $this->order($order, $query);
    }

    /**
     * Query data sâu hơn
     *
     * @param array $options
     * @return mixed
     */
    public function queryDeeper($options = [])
    {
        $query = $this->_model;
        if (!empty($options['select'])) {
            $query = $query->select($options['select']);
        }
        if (!empty($options['options'])) {
            $query = $this->queryOptionsDeeper($options['options'], $query);
        }
        if (!empty($options['with'])) {
            $query = $this->withDeeper($options['with'], $query);
        }
        if (!empty($options['where-has'])) {
            $query = $this->whereHasDeeper($options['where-has'], $query);
        }
        if (!empty($options['order'])) {
            $query = $this->order($options['order'], $query);
        } else {
            $query = $this->order([], $query);
        }
        return $query;
    }

    /**
     * Get first
     *
     * @param array $options
     * @param array $with
     */
    public function first($options = [], $with = [])
    {
        return $this->query($options, $with)->first();
    }

    /**
     * Get first theo query deeper
     *
     * @param array $options
     * @return mixed
     */
    public function firstDeeper($options = [])
    {
        return $this->queryDeeper($options)->first();
    }

    /**
     * Phân trang theo query
     *
     * @param $query
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function paginatedQuery($query, $page = 1, $limit = 20)
    {
        $page = $page ? (int)$page : 1;
        $limit = $limit ? (int)$limit : 20;

        if ($page <= 0) {
            $page = 1;
        }
        if ($limit > 100) {
            $limit = 100;
        }

        $query->take($limit);
        $query->skip(($page - 1) * $limit);
        return $query->get();
    }

    /**
     * Phân trang theo function query
     *
     * @param $options
     * @param int $page
     * @param int $limit
     * @param array $with
     * @param array $order
     * @return array
     */
    public function paginate($options, $page = 1, $limit = 20, $with = [], $order = [])
    {
        $query = $this->query($options, $with, $order);
        $data['total'] = $query->count();
        $data['data'] = $this->paginatedQuery($query, $page, $limit);
        return $data;
    }

    /**
     * Phân trang theo function query deeper
     *
     * @param $options
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function paginateDeeper($options, $page = 1, $limit = 20)
    {
        $query = $this->queryDeeper($options);
        $data['total'] = $query->count();
        $data['data'] = $this->paginatedQuery($query, $page, $limit);
        return $data;
    }

    /**
     * Order query
     *
     * @param array $order
     * @param null $query
     * @return mixed
     */
    public function order($order = [], $query = null)
    {
        if (!$query) {
            $query = $this->_model;
        }
        if (!empty($order)) {
            return $query->orderBy(array_key_first($order), end($order));
        }
        return $query->latest();
    }

    /**
     * Query options
     *
     * @param array $options
     * @param null $query
     * @return Model|mixed|null
     */
    public function queryOptions($options = [], $query = null)
    {
        if (!$query) {
            $query = $this->_model;
        }
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $query = $query->whereIn($key, $value);
            } else {
                $query = $query->where($key, $value);
            }
        }
        return $query;
    }

    /**
     * Các lựa chọn query where data
     *
     * @param $options
     * @param $query
     * @return mixed
     */
    public function queryOptionsDeeper($options = [], $query = null)
    {
        if (!$query) {
            $query = $this->_model;
        }
        if (!empty($options)) {
            foreach ($options as $item) {
                $opera = $item['opera'] ?? '=';
                switch ($opera) {
                    case 'like':
                        $query = $query->where($item['key'], 'like', '%' . $item['value'] . '%');
                        break;
                    case 'in':
                        $query = $query->whereIn($item['key'], $item['value']);
                        break;
                    case 'null':
                        $query = $query->whereNull($item['key']);
                        break;
                    case 'notNull':
                        $query = $query->whereNotNull($item['key']);
                        break;
                    case 'between':
                        $query = $query->whereBetween($item['key'], $item['value']);
                        break;
                    default:
                        $query = $query->where($item['key'], $opera, $item['value']);
                }

            }
        }
        return $query;
    }

    /**
     * With deeper
     *
     * @param array $with
     * @param null $query
     */
    public function withDeeper($with = [], $query = null)
    {
        if (!$query) {
            $query = $this->_model;
        }
        if (!isset($with['relation'])) {
            return $query->with($with);
        }
        return $query->with([$with['relation'] => function ($query) use ($with) {
            if (!empty($with['options'])) {
                $this->queryOptionsDeeper($with['options'], $query);
            }
        }]);
    }

    /**
     * WhereHas deeper
     *
     * @param array $where_has
     * @param null $query
     * @return mixed
     */
    public function whereHasDeeper($where_has = [], $query = null)
    {
        if (!$query) {
            $query = $this->_model;
        }
        if (!isset($where_has['relation'])) {
            return $query->whereHas($where_has, function ($query) {
            });
        }
        return $query->whereHas([$where_has['relation'] => function ($query) use ($where_has) {
            if (!empty($where_has['options'])) {
                $this->queryOptionsDeeper($where_has['options'], $query);
            }
        }]);
    }
}
