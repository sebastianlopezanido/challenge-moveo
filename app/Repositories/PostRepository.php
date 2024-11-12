<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostRepository
{
    protected $model;

    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    public function getAllPosts($limit, $page)
    {
        return $this->model->with('user')->paginate($limit, ['*'], 'page', $page);
    }

    public function createPost(array $data)
    {
        return $this->model->create($data);
    }

    public function findPostById($id)
    {
        return $this->model->with('user')->findOrFail($id);
    }

    public function updatePost(Post $post, array $data)
    {
        $post->update($data);
        return $post;
    }

    public function deletePostWithComments(Post $post)
    {
        $post->delete();
    }


}
