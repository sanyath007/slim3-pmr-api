<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\User;

class UserController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $users = User::all(['username', 'fullname', 'hospcode', 'position']);
        
        $data = json_encode($users, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getUser($request, $response, $args)
    {
        $user = User::where('username', $args['username'])
                    ->get(['username', 'name', 'hospcode', 'position'])
                    ->first();
                    
        $data = json_encode($user, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
}
