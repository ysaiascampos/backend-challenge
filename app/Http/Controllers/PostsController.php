<?php

namespace App\Http\Controllers;

use App\Models\Models\Post;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ConectarApiController;
use Illuminate\Support\Facades\Response;

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
                    'message' => 'Run no encontrado'
                ], 404);
            }
            $posts = $response->data;
            $count = 0;
            foreach ($posts as $postApi){
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
}
