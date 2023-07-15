<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * * Register New User
     */
    public function register (Request $request)
    {
        $input = $request->all();

        $rules = [
            'name' => 'required|string',
            'phone' => 'required|string|min:7|max:15',
            'email' => 'required|unique:users',
            'password' => 'required|confirmed',
            'role' => 'required|string'
        ];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $plainPass = $input['password'];

        $user = User::create([
            'name' => $input['name'],
            'phone' => $input['phone'],
            'email' => $input['email'],
            'password' => app('hash')->make($plainPass),
            'role' => $input['role']
        ]);

        if ($user) {
            $response = [
                'message' => 'Register Success',
                'status_code' => Response::HTTP_CREATED,
                'data' => $user
            ];

            return response()->json($response, Response::HTTP_CREATED);
        }

        $response = [
            'message' => 'Register Failed',
            'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ];

        return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * * Login User
     */
    public function login(Request $request)
    {
        $input = $request->all();

        $rules = [
            'email' => 'required|string',
            'password' => 'required|string'
        ];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $credentials = $request->only(['email', 'password']);

        // dd($credentials);
        if (!$token = Auth::setTTL(240)->attempt($credentials)) {
            $response = [
                'message' => 'Unauthorized',
                'status_code' => Response::HTTP_UNAUTHORIZED 
            ];

            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        $response = [
            'message' => 'Login Success',
            'status_code' => Response::HTTP_OK,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => Auth::factory()->getTTL() * 60
            ]
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * * Get Logged In Users
     */
    public function user(Request $request)
    {
        $user = Auth::user();

        $response = [
            'message' => 'Get User Success',
            'status_code' => Response::HTTP_OK,
            'data' => $user
        ];

        return response()->json($response, Response::HTTP_OK);
    }
}
