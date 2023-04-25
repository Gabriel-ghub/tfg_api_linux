<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user(){
        return response()->json(['message' => 'Estas son todas las peticiones', 'data' => 'hola'], 200);
    }
}
