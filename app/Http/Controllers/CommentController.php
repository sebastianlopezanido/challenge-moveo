<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 
use App\Http\Requests\CommentRequest;
use App\Traits\JsonResponseTrait;
use App\Jobs\SendCommentNotification;
use Illuminate\Support\Facades\Bus;



class CommentController extends Controller
{
    use AuthorizesRequests, JsonResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index($postId)
    {
         // Obtener todos los comentarios de un post específico
         $comments = Comment::where('post_id', $postId)->with('user')->get();
         return $this->successResponse($comments, 'Comments retrieved successfully');
        }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CommentRequest $request, $postId)
    {
        $post = Post::findOrFail($postId);

        $comment = Comment::create([
            'content' => $request->content,
            'post_id' => $postId,
            'user_id' => Auth::id(),
        ]);

        //trigger al job
        Bus::dispatch(new SendCommentNotification($post, $comment));

        return $this->successResponse($comment, 'Comment created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        return $this->successResponse($comment->load('user'), 'Comment retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $comment->update($request->only(['content']));

        return $this->successResponse($comment, 'Comment updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return $this->successResponse(null, 'Comment deleted successfully', 204);
    }
}
