<?php

namespace App\Modules\Story\Repositories\Eloquent;

use App\Modules\Story\Models\Story;
use App\Modules\Story\Repositories\Contracts\StoryRepositoryInterface;

class StoryRepository implements StoryRepositoryInterface
{
    /**
     * The model instance
     *
     * @var \App\Modules\Story\Models\Story
     */
    protected $model;

    /**
     * Create a new repository instance
     *
     * @param  \App\Modules\Story\Models\Story  $model
     */
    public function __construct(Story $model)
    {
        $this->model = $model;
    }

    /**
     * Get all stories
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll($columns = ['*'])
    {
        return $this->model->select($columns)->get();
    }

    /**
     * Get story by ID
     *
     * @param  int  $id
     * @return \App\Modules\Story\Models\Story|null
     */
    public function getById($id)
    {
        return $this->model->find($id);
    }

    /**
     * Create a new story
     *
     * @param  array  $data
     * @return \App\Modules\Story\Models\Story
     */
    public function create($data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a story
     *
     * @param  int  $id
     * @param  array  $data
     * @return \App\Modules\Story\Models\Story
     */
    public function update($id, $data)
    {
        $story = $this->model->find($id);
        if ($story) {
            $story->update($data);
        }
        return $story;
    }

    /**
     * Delete a story
     *
     * @param  int  $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->model->destroy($id) > 0;
    }
}
