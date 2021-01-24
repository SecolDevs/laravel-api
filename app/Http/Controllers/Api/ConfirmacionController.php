<?php

namespace App\Http\Controllers\Api;

use App\Models\Actividad;
use App\Models\Confirmacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConfirmacionController extends ApiController {
    // Create Confirmacion
    public function postConfirmacion(Request $request) {
        $validator = Validator::make($request->all(), [
            'iduser' => 'required',
            'idactividad' => 'required'
        ]);

        if ($validator->fails()) return
            $this->sendError('Error de validacion', $validator->errors(), 422);

        // Verificamos si la confirmacion a la actividad ya existe
        $confirmacion = Confirmacion::where(
            [
                ["iduser", "=", $request->get('iduser')],
                ["idactividad", "=", $request->get('idactividad')]
            ]
        )->first();

        if ($confirmacion !== null) return
            $this->sendError('Error de confirmacion', ['El usuario ya ha confirmado previamente'], 422);

        $confirmacion = new Confirmacion();
        $confirmacion->iduser = $request->get('iduser');
        $confirmacion->idactividad = $request->get('idactividad');
        $confirmacion->save();

        $data = ['confirmacion' => $confirmacion];

        return $this->sendResponse($data, 'Confirmacion creada correctamente');
    }

    // Listar Confirmaciones
    public function getConfirmaciones() {
        $data = [];
        $confirmaciones = Confirmacion::all();

        $data['confirmaciones'] = $confirmaciones;

        return $this->sendResponse($data, 'Confirmaciones recuperadas correctamente');
    }

    // Ver Confirmacion
    public function getConfirmacion($id) {
        // Verificar si los datos necesarios existen
        $confirmacion = Confirmacion::find($id);
        if ($confirmacion === null) return
            $this->sendError('Eror en los datos', ["La confirmacion no existe"], 404);

        $actividad = Actividad::find($confirmacion->idactividad);
        if ($actividad === null) return
            $this->sendError('Eror en los datos', ["La Actividad no existe"], 404);

        // Hacemos una consulta compleja con db
        $users = DB::table('confirm')
            ->where('confirm.idactividad', '=', $confirmacion->idactividad)
            ->join('userdata', 'confirm.iduser', 'userdata.iduser')
            ->select('userdata.iduser', 'userdata.nombre', 'userdata.foto', 'userdata.edad', 'userdata.genero')
            ->get();

        // Guardamos los datos para mostrarlos
        $data = [
            'actividad' => $actividad,
            'users' => $users
        ];

        return $this->sendResponse(
            [$data],
            "Confirmacion eliminada correctamente"
        );
    }

    // Obtener las confirmaciones de x usuario
    public function getConfirmacionUser($id) {
        $confirmaciones = Confirmacion::where('iduser', '=', $id)->get();

        $confirmaciones = DB::table('confirm')
            ->where('confirm.iduser', '=', $id)
            ->join('actividad', 'confirm.idactividad', 'actividad.id')
            ->select('confirm.id', 'actividad.id AS idactividad', 'actividad.nombre', 'actividad.nombre', 'actividad.descripcion', 'actividad.active', 'actividad.foto')
            ->get();

        $data = [
            'confirmaciones' => $confirmaciones
        ];

        return $this->sendResponse($data, 'Confirmaciones recuperadas correctamente');
    }

    // Update Confirmacion

    // Delete Confirmacion
    public function deleteConfirmacion($id) {
        $confirmacion = Confirmacion::find($id);
        if ($confirmacion === null) return
            $this->sendError('Error en los datos', ['El usuario no existe'], 404);

        $confirmacion->delete();

        return $this->sendResponse(
            [$id],
            "Confirmacion eliminada correctamente"
        );
    }
}
