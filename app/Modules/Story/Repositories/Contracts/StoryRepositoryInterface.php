<?php

namespace App\Modules\Story\Repositories\Contracts;

interface StoryRepositoryInterface
{
    /**
     * Get all stories
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll($columns = ['*']);

    /**
     * Get story by ID
     *
     * @param  int  $id
     * @return \App\Modules\Story\Models\Story|null
     */
    public function getById($id);

    /**
     * Create a new story
     *
     * @param  array  $data
     * @return \App\Modules\Story\Models\Story
     */
    public function create($data);

    /**
     * Update a story
     *
     * @param  int  $id
     * @param  array  $data
     * @return \App\Modules\Story\Models\Story
     */
    public function update($id, $data);

    /**
     * Delete a story
     *
     * @param  int  $id
     * @return bool
     */
    public function delete($id);
}
