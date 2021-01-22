<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController {

    // Test de Autenticacion
    public function testOauth() {
        $user = Auth::user();
        return $this->sendResponse(
            $user,
            "Ruta Segura"
        );
    }

    // Test ruta insegura
    public function test() {
        return $this->sendResponse(
            ['success' => 'ok'],
            "Ruta Insegura"
        );
    }

    // Registro de usuarios
    public function register(Request $request) {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'confirm_password' => 'required|same:password',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError(
                'Error de validacion',
                $validator->errors(),
                422
            );
        }

        $input = $request->all();
        $input["password"] = bcrypt($request->get("password"));
        // Crear nuevo usuario en la db
        $user = User::create($input);
        $token = $user->createToken('MyApp')->accessToken;

        $data = [
            'token' => $token,
            'user' => $user
        ];

        return $this->sendResponse(
            $data,
            "Usuario registrado correctamente"
        );
    }
}
