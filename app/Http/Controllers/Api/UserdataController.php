<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Userdata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserdataController extends ApiController {

    // Obtener todos los usuarios
    public function getUsers() {
        $data = [];
        //$users = Userdata::all();

        // Query para obtener usuarios anidados
        $users = DB::table('users')
            ->join('userdata', 'users.id', '=', 'userdata.iduser')
            ->select('users.id', 'userdata.nombre', 'userdata.foto', 'userdata.edad', 'userdata.genero')
            ->get();
        $data['users'] = $users;

        return $this->sendResponse(
            $data,
            "Usuarios recuperados correctamente"
        );
    }

    // Obtener un solo usuario
    public function getUser($id) {
        $user = new User();
        $userdata = Userdata::where('iduser', "=", $id)->first();
        $data = [];
        $data['user'] = $user->find($id);
        $data['userdata'] = $userdata;

        return $this->sendResponse(
            $data,
            "Usuario recuperado correctamente"
        );
    }

    // Crear nuevos usuarios
    public function postUser(Request $request) {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'confirm_password' => 'required|same:password',
                'edad' => 'required',
                'genero' => 'required',
                'acercade' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError(
                'Error de creacion',
                $validator->errors(),
                422
            );
        }

        // Procesa los datos del body
        $input = $request->all();
        $input["password"] = bcrypt($request->get("password"));
        // Crear nuevo usuario en la db
        $user = User::create($input);
        $token = $user->createToken('MyApp')->accessToken;

        // Crea los datos para guardar en la db
        $userdata = new Userdata();
        $userdata->nombre = $request->get('name');
        $userdata->foto = $request->get('foto');
        $userdata->edad = $request->get('edad');
        $userdata->genero = $request->get('genero');
        $userdata->acercade = $request->get('acercade');
        $userdata->iduser = $user->id;
        // Guarda en la db
        $userdata->save();

        $data = [
            'user' => $user,
            'userdata' => $userdata
        ];

        return $this->sendResponse(
            $data,
            "Usuario creado correctamente"
        );
    }

    // Modificar usuarios
    public function putUser($id, Request $request) {

        // Verificar si el registro existe
        $user = User::find($id);
        if ($user === null) {
            return $this->sendError(
                "Error en los datos",
                ["El usuario no existe"],
                422
            );
        }

        // Validacion del scheema
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'edad' => 'required',
                'genero' => 'required',
                'acercade' => 'required',
            ]
        );
        if ($validator->fails()) {
            return $this->sendError(
                'Error de creacion',
                $validator->errors(),
                422
            );
        }

        // Cambia el nombre en users
        $user->name = $request->get('name');
        $user->save();

        // Toma los datos de la tabla userdata
        $userdata = Userdata::where('iduser', "=", $id)->first();
        // Cambia los datos de userdata
        $userdata->nombre = $request->get('name');
        $userdata->edad = $request->get('edad');
        $userdata->genero = $request->get('genero');
        $userdata->acercade = $request->get('acercade');
        $userdata->save();

        $data = [
            'user' => $user,
            'userdata' => $userdata
        ];

        return $this->sendResponse(
            $data,
            "Usuario modificado correctamente"
        );
    }

    public function deleteUser($id) {
        // Verifica si el usuario existe
        $user = User::find($id);
        if ($user === null) {
            return $this->sendError(
                "Error en los datos",
                ["El usuario no existe"],
                422
            );
        }
        // Toma los datos de la tabla userdata
        $userdata = Userdata::where('iduser', "=", $id)->first();

        // Elimina los registros
        $user->delete();
        $userdata->delete();

        // Response
        $data = [
            'id' => $id,
        ];
        return $this->sendResponse(
            $data,
            "Usuario eliminado correctamente"
        );
    }
}
