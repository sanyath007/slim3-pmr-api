<?php

namespace App\Controllers\Auth;

use App\Controllers\Controller;
use Firebase\JWT\JWT;
use Tuupola\Base62;
use App\Models\User;

class LoginController extends Controller
{
    public function login($req, $res, $args)
    {
        $params = $req->getParsedBody() ? : [];

        if($this->auth->attempt($params['username'], $params['password'])) {
            $now = new \DateTime();
            $future = new \DateTime("+30 minutes");
            $jti = (new Base62)->encode(random_bytes(16));
            $user = $this->auth->getUser();

            $payload = [
                "iat"   => $now->getTimeStamp(),
                "exp"   => $future->getTimeStamp(),
                "jti"   => $jti,
                "sub"   => $user->username
            ];

            $secret = getenv("JWT_SECRET");            
            $token = JWT::encode($payload, $secret, "HS256");

            $data = [
                'token'         => $token,
                'expires_at'    => $future->getTimeStamp(),
                'user'          => [
                    'username'      => $user->username,
                    'email'         => $user->email,
                    'fullname'      => $user->fullname,
                    'position'      => $user->position,
                    'hospcode'      => $user->hospcode,
                    'permissions'   => count($user->permissions) > 0 ? $user->permissions[0] : null,
                ]
            ];

            return $res->withStatus(201)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        } else {
            $data = [
                'token'     => '',
                'message'   => '',
            ];       

            return $res->withStatus(401)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    }
    
    public function login2($req, $res, $args)
    {
        $params = $req->getParsedBody() ? : [];

        if($this->auth->attempt2($params['cid'], $params['phone'])) {
            $now = new \DateTime();
            $future = new \DateTime("+30 minutes");
            $jti = (new Base62)->encode(random_bytes(16));
            $user = $this->auth->getUser();

            $payload = [
                "iat"   => $now->getTimeStamp(),
                "exp"   => $future->getTimeStamp(),
                "jti"   => $jti,
                "sub"   => $user->cid
            ];

            $secret = getenv("JWT_SECRET");            
            $token = JWT::encode($payload, $secret, "HS256");

            $data = [
                'token'         => $token,
                'expires_at'    => $future->getTimeStamp(),
            ];

            return $res->withStatus(201)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        } else {
            $data = [
                'token'     => '',
                'message'   => '',
            ];       

            return $res->withStatus(401)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    }
}
