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
        $validator = Validator::make(['plate' => $request->plate], [
            'plate' => 'required|string|regex:/^[a-zA-Z0-9]+$/|max:11',
        ],[
            'plate.required' => 'La mtricula es requerida',
            'plate.string' =>'La matrícula debe ser solo texto',
            'plate.regex' =>'La matrícula solo puede contener números y letras',
            'plate.size' =>'Máximo 11 carácteres',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }
            $car = Car::where('plate', $request->get("plate"))->first();
            if ($car) {
                return response()->json($car, 200);
            } else {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'message' => 'Coche no encontrado'
                ], 404));
            }
    }


    public function store(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'plate' => 'required|string|unique:cars|max:11',
                'brand' => 'required|string|max:30',
                'model' => 'required|string|max:30',
            ], [
                'plate.required' => 'La placa es requerida',
                'plate.unique' => 'Ya existe un coche con esa placa',
                'brand.required' => 'La marca es requerida',
                'model.required' => 'El modelo es requerido',

                'plate.string' => 'La matricula debe ser tipo texto',
                'brand.string' => 'La marca debe ser tipo texto',
                'model.string' => 'El modelo debe ser tipo texto',

                'plate.max' => 'Máximo 11 carácteres',
                'brand.max' => 'Máximo 30 carácteres',
                'model.max' => 'Máximo 30 carácteres',            
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
    }


    public function modify(Request $request)
    {
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
    }

    public function delete(Request $request)
    {
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
    }
}
