<?php
namespace App\Repositories\Post;


interface PostRepositoryInterface {
    
    public function getPostStats(int $blogId) : array;

}