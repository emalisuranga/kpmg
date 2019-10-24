<?php

namespace App\Http\Controllers\Hash;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HashController extends Controller
{
    protected function hashText($text){
        return response()->json(['status' => 200, 'data' => bcrypt($text)]);
    }
}
