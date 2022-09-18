<?php

namespace App\Auth;

use App\Models\User;

class Auth
{
    protected $user;

    public function attempt($username, $password)
    {
        $user = User::where('username', $username)->first();

        if(!$user) {
            return false;
        }

        if(password_verify($password, $user->password)) {
            $this->user = $user;

            return true;
        }

        return false;
    }

    public function getUser()
    {
        if($this->user) {
            return User::with('position', 'permissions', 'permissions.role')
                    ->where('id', $this->user->id)
                    ->first();
        }
    }
}
