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

    public function index()
    {
        return Car::all();
    }


    public function show($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['error' => 'Coche no encontrado'], 404);
        }

        return response()->json($car);
    }
    public function search(Request $request)
    {
        $validator = Validator::make(['plate' => $request->plate], [
            'plate' => 'required|string|regex:/^[a-zA-Z0-9]+$/|max:11',
        ], [
            'plate.required' => 'La mtricula es requerida',
            'plate.string' => 'La matrícula debe ser solo texto',
            'plate.regex' => 'La matrícula solo puede contener números y letras',
            'plate.size' => 'Máximo 11 carácteres',
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
            'plate' => 'bail|required|string|unique:cars|max:11',
            'brand' => 'bail|required|string|max:30',
            'model' => 'bail|required|string|max:30',
        ], [
            'plate.required' => 'La placa es requerida.',
            'plate.unique' => 'Ya existe un coche con esa placa.',
            'brand.required' => 'La marca es requerida.',
            'model.required' => 'El modelo es requerido.',

            'plate.string' => 'La matrícula debe ser texto.',
            'brand.string' => 'La marca debe ser texto.',
            'model.string' => 'El modelo debe ser texto.',

            'plate.max' => 'Máximo 11 caracteres.',
            'brand.max' => 'Máximo 30 caracteres.',
            'model.max' => 'Máximo 30 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
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


    public function updateAndPlate(Request $request, $id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['error' => 'Coche no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'model' => 'bail|required|not_regex:/^\s*$/',
            'plate' => [
                'bail',
                'required',
                'unique:cars,plate,' . $request->plate,
                'not_regex:/^\s*$/',
            ],
            'brand' => 'bail|required|not_regex:/^\s*$/',
        ], [
            'model.required' => 'El modelo es requerido.',
            'plate.required' => 'La matrícula es requerida.',
            'plate.unique' => 'Ya existe un coche con esa matrícula.',
            'brand.required' => 'La marca es requerida.',
            'model.not_regex' => 'El modelo no puede contener solo espacios en blanco.',
            'plate.not_regex' => 'La matrícula no puede contener solo espacios en blanco.',
            'brand.not_regex' => 'La marca no puede contener solo espacios en blanco.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }

        $car->model = $request->model;
        $car->brand = $request->brand;
        $car->plate = $request->plate;

        $car->save();

        return response()->json(['car' => $car], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'model' => 'bail|required|not_regex:/^\s*$/',
            'brand' => 'bail|required|not_regex:/^\s*$/',
        ], [
            'model.required' => 'El modelo es requerido.',
            'brand.required' => 'La marca es requerida.',
            'model.not_regex' => 'El modelo no puede contener solo espacios en blanco.',
            'brand.not_regex' => 'La marca no puede contener solo espacios en blanco.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }

        $car = Car::find($id);

        if (!$car) {
            return response()->json(['error' => 'Coche no encontrado'], 404);
        }

        $car->model = $request->model;
        $car->brand = $request->brand;

        $car->save();

        return response()->json(['car' => $car], 200);
    }


    public function delete($car_id)
    {
        $validator = Validator::make(['car_id' => $car_id], [
            'car_id' => 'required|exists:cars,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $car = Car::find($car_id);

        if ($car) {
            $car->delete();
            return response()->json(['message' => 'Se encontró el coche', 'El coche que borró fue:' => $car], 200);
        } else {
            return response()->json(['message' => 'Error al encontrar el coche'], 404);
        }
    }
}
