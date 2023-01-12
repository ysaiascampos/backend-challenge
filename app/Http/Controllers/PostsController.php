<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ConectarApiController;
use Illuminate\Support\Facades\Response;
Use DB;

class PostsController extends Controller
{

    public function updatePostsToApi(){
        try {
            $response = ConectarApiController::consumoConGuzzle("GET", "/posts");
            if (!in_array($response->statusCode, [
                200,
                201
            ])) {
                return response()->json([
                    'message' => 'Error no encontrado'
                ], 404);
            }
            $postsApi = $response->data;
            $count = 0;
            foreach ($postsApi as $postApi){
                $post = Post::where('id', $postApi['id'])
                    ->first();
                $numberTitle = str_word_count($postApi['title'], 0);
                $numberBody = str_word_count($postApi['body'], 0);
                $rating = ($numberTitle*2) + $numberBody;
                if ($post) {
                    $postUpdate = Post::where('id', $post['id']);
                    $postUpdate->update(array('body' => $postApi['body']));
                } else {
                    $post = new Post();
                    $post->id = $postApi['id'];
                    $post->user_id = $postApi['userId'];
                    $post->title = $postApi['title'];
                    $post->body = $postApi['body'];
                    $post->rating = $rating;
                    $post->save();
                }
                $count++;
                if($count>49){
                    break;
                }
            }
            $posts = Post::all();

        } catch (Exception $ex) {
            log::error($ex->getMessage());
        }
        return response()->json([
            'data' => $posts
        ], 200);

    }

    public function getBestPosts(){
        $users = DB::select("SELECT users.name, posts.id, posts.title, posts.body, posts.rating
                FROM users
                inner Join posts on posts.user_id = users.id
                GROUP BY users.name
                HAVING max(posts.rating) = posts.rating");

        return response()->json([
            'data' => $users
        ], 200);
    }

    public function getPost($id){
        $post = Post::where('posts.id', $id)
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->select('users.name','posts.id', 'posts.title', 'posts.body')
            ->first();
        if(!$post){
            return response()->json([
                'message' => 'si no existe'
            ], 404);
        }
        return response()->json([
            'data' => $post
        ], 200);
    }
}
