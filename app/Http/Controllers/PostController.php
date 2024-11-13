<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use App\Services\PostService;

/**
 * @OA\Tag(
 *     name="Posts",
 *     description="API Endpoints for managing posts"
 * )
 */
class PostController extends Controller
{
    protected $postService;

    use AuthorizesRequests, JsonResponseTrait; 

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

     /**
     * @OA\Get(
     *     path="/posts",
     *     tags={"Posts"},
     *     summary="Get paginated list of posts",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of posts per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Current page",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Posts retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Posts retrieved successfully")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/posts",
     *     tags={"Posts"},
     *     summary="Create a new post",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", example="New Post Title"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Post created successfully")
     *         )
     *     )
     * )
     */
    public function store(PostRequest $request)
    {
        $post = $this->postService->createNewPost($request->validated());

        return $this->successResponse($post, 'Post created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/posts/{id}",
     *     tags={"Posts"},
     *     summary="Get a specific post by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Post retrieved successfully")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $post = $this->postService->getPostById($id);
        return $this->successResponse($post, 'Post retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/posts/{id}",
     *     tags={"Posts"},
     *     summary="Update a specific post",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", example="Updated Post Title"),
     *             @OA\Property(property="content", type="string", example="Updated content of the post.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Post updated successfully")
     *         )
     *     )
     * )
     */
    public function update(PostRequest $request, Post $post)
    {
        // Usa la policy para autorizar la actualizacion
        $this->authorize('update', $post);

        $updatedPost = $this->postService->updatePost($post, $request->only(['title', 'content']));

        return $this->successResponse($updatedPost, 'Post updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/posts/{id}",
     *     tags={"Posts"},
     *     summary="Delete a specific post",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Post deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy(Post $post)
    {
        // Usa la policy para autorizar la eliminación
        $this->authorize('delete', $post);

        $this->postService->deletePost($post);
    
        return $this->successResponse(null, 'Post deleted successfully', 204);
    }
}
