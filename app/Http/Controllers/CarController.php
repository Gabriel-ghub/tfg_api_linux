<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Psy\Readline\Hoa\_Protocol;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    public function search(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        if ($role_id == 1) {
            $car = Car::where('plate', $request->get("plate"))->first();
            if ($car) {
                return response()->json($car, 200);
            } else {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'message' => 'Coche no encontrado'
                ], 404));
            }
        } else {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción'], 400);
        }
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;

        if ($role_id == 1) {
            $validator = Validator::make($request->all(), [
                'plate' => 'required|string|unique:cars',
                'brand' => 'required|string',
                'model' => 'required|string',
            ], [
                'plate.required' => 'La placa es requerida',
                'plate.unique' => 'Ya existe un coche con esa placa',
                'brand.required' => 'La marca es requerida',
                'model.required' => 'El modelo es requerido',
            ]);

            // Si hay errores, devolver la respuesta con los errores
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Crear el nuevo coche con los datos validados
            $car = Car::create([
                'plate' => $request->plate,
                'brand' => $request->brand,
                'model' => $request->model,
            ]);

            // Devolver una respuesta con el coche creado
            return response()->json(['car' => $car], 201);

        } else {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción'], 402);
        }
    }


    public function modify(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;

        if ($role_id == 1) {
            $validator = Validator::make($request->all(), [
                'plate' => 'required|string',
                'brand' => 'required|string',
                'model' => 'required|string',
                'id_car' => 'required|string',

            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $car = Car::find($request->id_car);

            if ($car) {
                $car->plate = $request->plate;
                $car->brand = $request->brand;
                $car->model = $request->model;
                return response()->json(['message' => 'Se encontró el coche', 'Datos actualizados:' => $car], 200);
            } else {
                return response()->json(['message' => 'Error al encontrar el coche'], 200);
            }
        } else {
            return response()->json(['message' => 'PASA ALGO RARO'], 200);
        }
    }

    public function delete(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;

        if ($role_id == 1) {
            $validator = Validator::make($request->all(), [
                'plate' => 'required|string',
                'id_car' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $car = Car::find($request->id_car);

            if ($car) {
                $car->delete();
                return response()->json(['message' => 'Se encontró el coche', 'El coche que borró fue:' => $car], 200);
            } else {
                return response()->json(['message' => 'Error al encontrar el coche'], 200);
            }
        } else {
            return response()->json(['message' => 'No está autorizado a realizar esta acción'], 200);
        }
    }
}
