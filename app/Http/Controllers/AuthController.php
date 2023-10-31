<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountLogin;
use App\Http\Requests\AccountRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(AccountLogin $request)
    {
        $validated = $request->validated();
        if (Auth::attempt($validated)) {
            $response['token'] = Auth::user()->createToken('lyly_store')->accessToken;
            return $this->sendResponse($response, 'Đăng nhập thành công');
        } else {
            return $this->sendError('Đăng nhập thất bại');
        }
    }

    public function register(AccountRegister $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        if ($user) {
            return response()->json(['user' => $user, 'message' => 'Tạo thành công']);
        } else {
            return response()->json(['user' => $user, 'message' => 'Tạo that bai']);

        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
