<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use App\Services\PostService;

class PostController extends Controller
{
    protected $postService;

    use AuthorizesRequests, JsonResponseTrait; 

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);

        $posts = $this->postService->getPaginatedPosts($limit, $page);

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
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request)
    {
        $post = $this->postService->createNewPost($request->validated());

        return $this->successResponse($post, 'Post created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = $this->postService->getPostById($id);
        return $this->successResponse($post, 'Post retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $request, Post $post)
    {
        // Usa la policy para autorizar la actualizacion
        $this->authorize('update', $post);

        $updatedPost = $this->postService->updatePost($post, $request->only(['title', 'content']));

        return $this->successResponse($updatedPost, 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Usa la policy para autorizar la eliminación
        $this->authorize('delete', $post);

        $this->postService->deletePost($post);
    
        return $this->successResponse(null, 'Post deleted successfully', 204);
    }
}
