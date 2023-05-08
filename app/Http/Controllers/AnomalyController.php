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