<?php

namespace App\Modules\User\Repositories\Eloquent;

use App\Models\User;
use App\Modules\User\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * The model instance
     *
     * @var \App\Models\User
     */
    protected $model;

    /**
     * Create a new repository instance
     *
     * @param  \App\Models\User  $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get all users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Get user by ID
     *
     * @param  int  $id
     * @return \App\Models\User|null
     */
    public function getById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Get user by email
     *
     * @param  string  $email
     * @return \App\Models\User|null
     */
    public function getByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Create a new user
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    public function create($data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a user
     *
     * @param  int  $id
     * @param  array  $data
     * @return \App\Models\User
     */
    public function update($id, $data)
    {
        $user = $this->model->find($id);
        if ($user) {
            $user->update($data);
        }
        return $user;
    }

    /**
     * Delete a user
     *
     * @param  int  $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->model->destroy($id) > 0;
    }
}
