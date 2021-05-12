<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;

/**
 * Class LoginController
 * @package App\Http\Controllers
 */
class LoginController extends Controller {


    /**
     * @param \Illuminate\Http\Request $request
     * @return string
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(Request $request): string
    {

        $request->validate(
            [
                'email'    => 'required|email',
                'password' => 'required'
            ]
        );

        $user = User::where('email', $request->email)->first();

        if (!$user || !\Hash::check($request->password, $user->password))
        {
            throw ValidationException::withMessages([
                'email' => [ 'The provided credentials are incorrect.' ],
            ]);
        }

        return $user->createToken($request->email, [ 'system:access' ])->plainTextToken;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function check(Request $request): mixed
    {
        return $request->user()->tokenCan('system:access');
    }


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([ 'message' => 'Successfuly logged out' ], JsonResponse::HTTP_OK);
    }
}
