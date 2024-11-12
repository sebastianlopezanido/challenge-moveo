<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCommentNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $post;
    protected $comment;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post, Comment $comment)
    {
        $this->post = $post;
        $this->comment = $comment;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        //uso un log para mostrar la funcionalidad, pero acá se deberia conectar a un servicio real de notificaciones
        Log::info("Loco mira el comentario que te dejaron en el post '{$this->post->title}': {$this->comment->content}");
    }

    public function getPost()
    {
        return $this->post;
    }

    public function getComment()
    {
        return $this->comment;
    }
}
