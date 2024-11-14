<?php

namespace App\Services;

use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Traits\JsonResponseTrait;
use Exception;
use Illuminate\Support\Facades\Log;


class PostService
{
    use JsonResponseTrait;
    protected $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function getPaginatedPosts($limit, $page)
    {
        try {

            $posts = $this->postRepository->getAllPosts($limit, $page);

            return $this->successResponse([
                'posts' => $posts->items(),
                'links' => [
                    'first' => $posts->url(1),
                    'last' => $posts->url($posts->lastPage()),
                    'prev' => $posts->previousPageUrl(),
                    'next' => $posts->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'from' => $posts->firstItem(),
                    'last_page' => $posts->lastPage(),
                    'path' => $posts->path(),
                    'per_page' => $posts->perPage(),
                    'to' => $posts->lastItem(),
                    'total' => $posts->total(),
                ]
            ], 'Posts retrieved successfully');

        } catch (Exception $e) {
            Log::error('Error in getPaginatedPosts: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function createNewPost(array $data)
    {
        try{

            $data['user_id'] = Auth::id(); // relación con user
            $post = $this->postRepository->createPost($data);
            return $this->successResponse($post, 'Post created successfully', 201);

        } catch (Exception $e) {

            Log::error('Error in createNewPost: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 422);

        }
        
    }

    public function getPostById($id)
    {        
        try{

            $post = $this->postRepository->findPostById($id);
            return $this->successResponse($post, 'Post retrieved successfully');

        } catch (Exception $e) {

            Log::error('Error in getPostById: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 422);

        }
    }

    public function updatePost(Post $post, array $data)
    {
        try{

            $updatedPost=$this->postRepository->updatePost($post, $data);
            return $this->successResponse($updatedPost, 'Post updated successfully');

        } catch (Exception $e) {

            Log::error('Error in updatePost: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 422);

        }
    }

    public function deletePost(Post $post)
    {
        try{
            $this->postRepository->deletePostWithComments($post);
            return $this->successResponse(null, 'Post deleted successfully', 204);

        } catch (Exception $e) {

            Log::error('Error in deletePost: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 422);

        }

    }


}
