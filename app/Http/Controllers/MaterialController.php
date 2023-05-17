<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function deleteMaterial(Request $request, $id)
    {
        // Buscar el material por ID
        $material = Material::find($id);

        if (!$material) {
            // Si el material no existe, retornar una respuesta con el mensaje correspondiente
            return response()->json(['error' => 'Material no encontrado'], 404);
        }

        // Eliminar el material de la base de datos
        $material->delete();

        // Retornar una respuesta con el mensaje de éxito
        return response()->json(['message' => 'Material eliminado correctamente'], 200);
    }


    public function createMaterial(Request $request)
    {
        // Validar los datos de entrada con mensajes personalizados
        // Validar los datos de entrada con mensajes personalizados


        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'order_id' => 'required|exists:orders,id',
            'quantity' => 'integer|min:1',
        ], [
            'description.required' => 'La descripción es obligatoria.',
            'order_id.required' => 'El ID de la orden es obligatorio.',
            'order_id.exists' => 'La orden especificada no existe.',
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min' => 'La cantidad debe ser al menos :min.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 409);
        }
        // Crear el nuevo material
        $material = new Material();
        $material->description = $request->description;
        $material->order_id = $request->order_id;
        $material->quantity = $request->quantity;
        $material->save();

        // Obtener todos los trabajos asociados a la orden
        $order = Order::findOrFail($request->order_id);
        $materials = $order->materials;

        // Retornar una respuesta con los trabajos asociados a la orden
        return response()->json($materials, 200);
    }

    public function updateMaterialPrice(Request $request)
    {
        // Validación de los datos recibidos
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|exists:materials,id',
            'price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 409);
        }

        // Obtener el material
        $material = Material::find($request->material_id);

        // Actualizar el precio del material
        $material->price = $request->price;
        $material->save();

        return response()->json($material, 200);
    }
}
