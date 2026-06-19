<?php

namespace App\Modules\Story\Controllers;

use App\Shared\Controllers\BaseController;
use App\Modules\Story\Repositories\Contracts\StoryRepositoryInterface;
use Illuminate\Http\Request;

class StoryController extends BaseController
{
    /**
     * The story repository instance
     *
     * @var \App\Modules\Story\Repositories\Contracts\StoryRepositoryInterface
     */
    protected $storyRepository;

    /**
     * Create a new controller instance
     *
     * @param  \App\Modules\Story\Repositories\Contracts\StoryRepositoryInterface  $storyRepository
     */
    public function __construct(StoryRepositoryInterface $storyRepository)
    {
        $this->storyRepository = $storyRepository;
    }

    /**
     * Get all stories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $stories = $this->storyRepository->getAll();
            return $this->success($stories, 'Stories retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve stories', 500);
        }
    }

    /**
     * Get a single story
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $story = $this->storyRepository->getById($id);
            if (!$story) {
                return $this->error('Story not found', 404);
            }
            return $this->success($story, 'Story retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve story', 500);
        }
    }

    /**
     * Create a new story
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'author_id' => 'required|exists:users,id',
            ]);

            $story = $this->storyRepository->create($validated);
            return $this->success($story, 'Story created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create story', 500);
        }
    }

    /**
     * Update a story
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
            ]);

            $story = $this->storyRepository->update($id, $validated);
            if (!$story) {
                return $this->error('Story not found', 404);
            }
            return $this->success($story, 'Story updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update story', 500);
        }
    }

    /**
     * Delete a story
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->storyRepository->delete($id);
            if (!$deleted) {
                return $this->error('Story not found', 404);
            }
            return $this->success(null, 'Story deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete story', 500);
        }
    }
}
