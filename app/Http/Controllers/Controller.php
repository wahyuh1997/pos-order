<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    function return_success($message, $data){
        $data = [
            'message' => $message,
            'data' => $data,
            'status' => true,
        ];
        return \response()->json($data);
    }
    
    function return_failed($message){
        $data = [
            'message' => $message,
            'status' => false,
        ];
        return \response()->json($data);
    }
}
