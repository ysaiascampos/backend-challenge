<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
Use DB;

class UserController extends Controller
{
    public function createUserToApi(){
        try {
            $response = ConectarApiController::consumoConGuzzle("GET", "/users");
            if (!in_array($response->statusCode, [
                200,
                201
            ])) {
                return response()->json([
                    'message' => 'Run no encontrado'
                ], 404);
            }
            $usersApi = $response->data;
            $count = 0;
            foreach ($usersApi as $userApi){
                $post = Post::where('user_id', $userApi['id'])
                    ->first();
                if ($post) {
                    $post = User::where('id', $userApi['id'])
                        ->first();
                    if (!$post) {
                        $user = new User();
                        $user->name = $userApi['name'];
                        $user->email = $userApi['email'];
                        $user->city = $userApi['address']['city'];
                        $user->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
                        $user->save();
                    }
                }
            }
            $users = User::all();

        } catch (Exception $ex) {
            log::error($ex->getMessage());
        }
        return response()->json([
            'data' => $users
        ], 200);

    }

    public function getUsersPosts(){
        $users = DB::select("SELECT users.id, users.name, users.email, users.city, AVG(posts.rating) as 'Promedio'
                FROM `users`
                inner Join posts on posts.user_id = users.id
                GROUP BY users.id, users.name, users.email, users.city
                order by AVG(posts.rating)");
        foreach ($users as $key => $user){
            $users[$key]->posts = Post::where('user_id',$user->id)->get();
        }

        return response()->json([
            'data' => $users
        ], 200);
    }
}
