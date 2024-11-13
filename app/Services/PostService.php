<?php

namespace App\Services;

use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostService
{
    protected $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function getPaginatedPosts($limit, $page)
    {
        return $this->postRepository->getAllPosts($limit, $page);
    }

    public function createNewPost(array $data)
    {
        $data['user_id'] = Auth::id(); // relación con user
        return $this->postRepository->createPost($data);
    }

    public function getPostById($id)
    {
        return $this->postRepository->findPostById($id);
    }

    public function updatePost(Post $post, array $data)
    {
        return $this->postRepository->updatePost($post, $data);
    }

    public function deletePost(Post $post)
    {
        return $this->postRepository->deletePostWithComments($post);

    }


}
