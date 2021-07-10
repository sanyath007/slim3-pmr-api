<?php

namespace App\Controllers;

use App\Controllers\Controller;

class HomeController extends Controller
{
    public function home($request, $response, $args)
    {
        return $response->withJson([
            'page' => 'Home page',
            'body' => 'This is Home page.'
        ]);
    }
}
