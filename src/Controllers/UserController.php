<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\PermissionRole;
use App\Models\Position;

class UserController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $users = User::with('position', 'permissions', 'permissions.role')->get();
        
        $data = json_encode($users, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getById($request, $response, $args)
    {
        $user = User::with('position', 'permissions', 'permissions.role')->find($args['id']);
                    
        $data = json_encode($user, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getInitForm($request, $response, $args)
    {
        $data = json_encode([
            'positions' => Position::all(),
            'roles'     => PermissionRole::all()
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();
            $uploadedFiles = $request->getUploadedFiles();

            $user = new User;
            $user->fullname     = $post['fullname'];
            $user->email        = $post['email'];
            $user->username     = $post['username'];
            $user->password     = password_hash($post['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $user->hospcode     = $post['hospcode'];
            $user->position_id  = $post['position_id'];

            /** Upload avatar's image */
            $uploadDir = APP_ROOT_DIR . '/public/uploads/avatars';
            foreach($uploadedFiles as $file) {
                $user->avatar   = moveUploadedFile($uploadDir, $file);
            }

            if ($user->save()) {
                $permission = new UserPermission;
                $permission->user_id    = $user->id;
                $permission->role       = $post['role_id'];
                $permission->save();

                $data = json_encode($user, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

                return $response->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write($data);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function update($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();

            $user = User::find($args['id']);
            $user->fullname     = $post['fullname'];
            $user->email        = $post['email'];
            $user->hospcode     = $post['hospcode'];
            $user->position_id  = $post['position_id'];

            if ($user->save()) {
                $permission = UserPermission::where('user_id', $args['id'])->first();
                $permission->role = $post['role_id'];
                $permission->save();

                $data = json_encode($user, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

                return $response->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write($data);
            }
        } catch (\Exception $ex) {
            //throw $ex;
        }
    }

    public function delete($request, $response, $args)
    {
        try {
            $user = User::find($args['id']);

            if ($user->delete()) {
                $data = json_encode($user, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

                return $response->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write($data);
            }
        } catch (\Exception $ex) {
            //throw $ex;
        }
    }
}
