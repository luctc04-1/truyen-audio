<?php

namespace App\Modules\User\Repositories\Contracts;

interface UserRepositoryInterface
{
    /**
     * Get all users
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll();

    /**
     * Get user by ID
     *
     * @param  int  $id
     * @return \App\Models\User|null
     */
    public function getById($id);

    /**
     * Get user by email
     *
     * @param  string  $email
     * @return \App\Models\User|null
     */
    public function getByEmail($email);

    /**
     * Create a new user
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    public function create($data);

    /**
     * Update a user
     *
     * @param  int  $id
     * @param  array  $data
     * @return \App\Models\User
     */
    public function update($id, $data);

    /**
     * Delete a user
     *
     * @param  int  $id
     * @return bool
     */
    public function delete($id);
}
