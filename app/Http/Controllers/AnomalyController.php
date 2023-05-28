<?php

namespace App\Http\Controllers;

use App\Models\Anomaly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AnomalyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $anomalias = $request->anomalias;
        $order_id = $request->order_id;


        foreach ($anomalias as $anomaly) {
            $anomaly = Anomaly::create([
                'order_id' => $order_id,
                'description' => $anomaly,
            ]);
        }
        return response()->json([$order_id], 200);
    }
    public function createOne(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'order_id' => ['bail', 'required', 'exists:orders,id'],
                'description' => ['bail', 'required', 'string', 'max:255'],
            ],
            [
                'order_id.required' => 'El campo nombre es obligatorio.',
                'order_id.exists' => 'La orden no existe.',
                'name.regex' => 'El campo nombre solo puede contener letras y espacios en blanco.',
                'name.min' => 'El campo nombre debe tener al menos 3 caracteres.',
                'description.max' => 'La descripcion no puede tener más de 255 caracteres.',
                'description.required' => 'La descripcion es obligatoria.',
                'description.string' => 'La descripcion debe ser una cadena de caracteres.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 409);
        }

        $anomaly = Anomaly::create([
            'order_id' => $request->order_id,
            'description' => $request->description,
        ]);
        return response()->json($anomaly, 200);
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $description = $request->description;
        try {
            $anomalia = Anomaly::find($id);
            if ($anomalia) {
                $anomalia->description = $description;
                $anomalia->save();
                return response()->json(["mensaje" => "La descripción de la anomalía con ID $id ha sido actualizada correctamente."], 200);
            } else {
                return response()->json(["mensaje" => "No se encontró la anomalía con ID $id."], 404);
            }
        } catch (\Exception $e) {
            return response()->json(["mensaje" => "Ocurrió un error al actualizar la descripción de la anomalía con ID $id: " . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->id;
        $anomaly = Anomaly::find($id);
        if (!$anomaly) {
            return response()->json(['message' => 'Anomaly not found'], 404);
        }
        $anomaly->delete();
        return response()->json(['message' => 'Anomaly deleted successfully'], 200);
    }
}
