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


/**
 * @OA\Tag(
 *     name="Comments",
 *     description="API Endpoints for managing comments"
 * )
 */
class CommentController extends Controller
{
    use AuthorizesRequests, JsonResponseTrait;

    /**
     * @OA\Get(
     *     path="/posts/{postId}/comments",
     *     tags={"Comments"},
     *     summary="Get all comments for a specific post",
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="message", type="string", example="Comments retrieved successfully")
     *         )
     *     )
     * )
     */
    public function index($postId)
    {
         // Obtener todos los comentarios de un post específico
         $comments = Comment::where('post_id', $postId)->with('user')->get();
         return $this->successResponse($comments, 'Comments retrieved successfully');
        }

    /**
     * @OA\Post(
     *     path="/posts/{postId}/comments",
     *     tags={"Comments"},
     *     summary="Create a new comment on a specific post",
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         required=true,
     *         description="ID of the post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="This is a comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Comment created successfully")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/comments/{comment}",
     *     tags={"Comments"},
     *     summary="Get a specific comment by ID",
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="ID of the comment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Comment retrieved successfully")
     *         )
     *     )
     * )
     */
    public function show(Comment $comment)
    {
        return $this->successResponse($comment->load('user'), 'Comment retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/comments/{comment}",
     *     tags={"Comments"},
     *     summary="Update a specific comment",
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="ID of the comment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Updated comment content")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Comment updated successfully")
     *         )
     *     )
     * )
     */
    public function update(CommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $comment->update($request->only(['content']));

        return $this->successResponse($comment, 'Comment updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/comments/{comment}",
     *     tags={"Comments"},
     *     summary="Delete a specific comment",
     *     @OA\Parameter(
     *         name="comment",
     *         in="path",
     *         required=true,
     *         description="ID of the comment",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Comment deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy( Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return $this->successResponse(null, 'Comment deleted successfully', 204);
    }
}
