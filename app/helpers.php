<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

if (!function_exists('compile_response')) {
    function compile_response($message, $status_code, $data = null)
    {
        $response = compact('message', 'status_code');
        if ($data) {
            $response += compact('data');
        }

        return $response;
    }
}

if (!function_exists('validate_role')) {
    function validate_role($roles)
    {
        $allow = false;
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if (Gate::allows($role)) {
                    $allow = true;
                    break;
                }
            }
        } else {
            $allow = Gate::denies($roles) ? false : true;
        }

        if (!$allow) {
            $message = compile_response("Unauthorized", Response::HTTP_UNAUTHORIZED);
            return response()->json($message, $message['status_code']);
        }
    }
}
