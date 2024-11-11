<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\JsonResponseTrait;

class PostController extends Controller
{
    use AuthorizesRequests, JsonResponseTrait; 

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Listado de posts con paginación
        $posts = Post::with('user')->paginate(10);
        return $this->successResponse($posts, 'Posts retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest  $request)
    {

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => Auth::id(),
        ]);

        return $this->successResponse($post, 'Post created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return $this->successResponse($post->load('user'), 'Post retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest  $request, Post $post)
    {
        // Usa la policy para autorizar la actualización
        $this->authorize('update', $post);

        $post->update($request->only(['title', 'content']));

        return $this->successResponse($post, 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Usa la policy para autorizar la eliminación
        $this->authorize('delete', $post);
        $post->delete();

        return $this->successResponse(null, 'Post deleted successfully', 204);
    }
}
