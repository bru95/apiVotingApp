<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email'    => 'required',
            'password' => 'required',
        ];

        $input = $request->only(["name", "email", "password"]);
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            $response = ["message" => $validator->errors()];
                return response()->json($response);
       }

        $name = $request->name;
        $email    = $request->email;
        $password = $request->password;

        try{
            $user = User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);

            if($user) {
                return $this->login($request);
            } else {
                $response = ["message" => "It was not possible to insert a client in the database"];
                return response()->json($response, 400);
            }
        } catch (Exception $e) {
            $response = ["message" => "It was not possible to insert a client in the database", "message_excpt" => $e->getMessage()];
            return response()->json($response, 500);
        }
    }

    /**
     * Get a JWT via given credentials.
    */
    public function login(Request $request){
    	$req = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:5',
        ]);

        if ($req->fails()) {
            return response()->json($req->errors(), 422);
        }

        if (! $token = Auth::attempt($req->validated())) {
            return response()->json(['Auth error' => 'Unauthorized'], 401);
        }

        return $this->generateToken($token);
    }


    /**
     * Sign out
    */
    public function signout() {
        Auth::logout();
        return response()->json(['message' => 'User loged out']);
    }

    /**
     * Token refresh
    */
    public function refresh() {
        return $this->generateToken(Auth::refresh());
    }

    /**
     * User
    */
    public function user() {
        return response()->json(auth()->user());
    }

    /**
     * Generate token
    */
    protected function generateToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
    
}
