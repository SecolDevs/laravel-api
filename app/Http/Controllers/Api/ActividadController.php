<?php

namespace App\Http\Controllers\Api;

use App\Models\Actividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ActividadController extends ApiController {

    // Get all actividades
    public function getActividades() {
        $data = [];
        $actividades = DB::table('actividad')
            ->select('actividad.id', 'actividad.nombre', 'actividad.foto', 'actividad.fecha')
            ->get();

        $data['actividades'] = $actividades;
        return $this->sendResponse($data, 'Actividades recuperadas corectamente');
    }

    // Get una actividad
    public function getActividad($id) {
        $actividad = Actividad::find($id);
        if ($actividad === null) return
            $this->sendError(
                "Error en los datos",
                ["La actividad no existe"],
                404
            );

        $confirmaciones = DB::table('confirm')
            ->where('confirm.idactividad', '=', $id)
            ->join('userdata', 'confirm.iduser', 'userdata.iduser')
            ->select('userdata.iduser', 'userdata.nombre', 'userdata.foto', 'userdata.edad', 'userdata.genero')
            ->get();

        $data = [
            'actividad' => $actividad,
            'usuarios' => $confirmaciones
        ];


        return $this->sendResponse($data, 'Actividades recuperadas corectamente');
    }

    // Create actividad
    public function postActividad(Request $request) {
        $validator = Validator::make(
            $request->all(),
            [
                'nombre' => 'required|unique:actividad',
                'foto' => 'required',
                'descripcion' => 'required',
                'fecha' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError(
                'Error de creacion',
                $validator->errors(),
                422
            );
        }

        // Crea los datos para guardar en la db
        $actividad = new Actividad();
        $actividad->nombre = $request->get('nombre');
        $actividad->foto = $request->get('foto');
        $actividad->descripcion = $request->get('descripcion');
        $actividad->fecha = $request->get('fecha');

        // Guarda en la db
        $actividad->save();

        $data = [
            'actividad' => $actividad,
        ];

        return $this->sendResponse(
            $data,
            "Actividad creada correctamente"
        );
    }

    // Update Actividad
    public function putActividad($id, Request $request) {
        // Verificar si el registro existe
        $actividad = Actividad::find($id);
        if ($actividad === null) {
            return $this->sendError(
                "Error en los datos",
                ["La actividad no existe"],
                404
            );
        }

        $validator = Validator::make(
            $request->all(),
            [
                'nombre' => 'required|unique:actividad',
                'foto' => 'required',
                'descripcion' => 'required',
                'fecha' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError(
                'Error de validacion',
                $validator->errors(),
                422
            );
        }

        // Toma los datos de la tabla Actividad
        $actividad = Actividad::where('id', "=", $id)->first();
        // Cambia los datos de actividad
        $actividad->nombre = $request->get('nombre');
        $actividad->foto = $request->get('foto');
        $actividad->descripcion = $request->get('descripcion');
        $actividad->fecha = $request->get('fecha');
        $actividad->save();

        $data = [
            'actividad' => $actividad,
        ];

        return $this->sendResponse(
            $data,
            "Actividad modificada correctamente"
        );
    }

    // Delete Actividad
    public function deleteActividad($id) {
        // Verifica si existe la actividad
        $actividad = Actividad::find($id);
        if ($actividad === null) return $this->sendError(
            "Error en los datos",
            ["La actividad no existe"],
            404
        );


        // Deshabilita el registro
        $actividad->active = 0;
        $actividad->save();

        // Response
        $data = [
            'id' => $id,
        ];
        return $this->sendResponse(
            $data,
            "Actividad deshabilitada correctamente"
        );
    }
}
