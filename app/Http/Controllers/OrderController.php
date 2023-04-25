<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    //TODO: "REALIZAR MIDDLEWARE QUE VERIFIQUE QUE EL ROL DEL USUARIO SEA 1"
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    public function showAll()
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        if ($role_id == 1) {
            $orders = Order::orderBy('id', 'desc')->get();
            return response()->json($orders, 200);
        } else {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción'], 200);
        }
    }

    public function getOrder($orderNumber)
    {
        $user = Auth::user();
        $role_id = $user->role_id;

        if ($role_id == 1) {
            $order = Order::where('id', $orderNumber)->first();
            $anomalies = $order->anomalies;

            if ($order) {
                return response()->json([
                    'success' => true,
                    'order' => $order
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ]);
            }
        } else {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción'], 200);
        }
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;

        if ($role_id == 1) {
            $validator = Validator::make($request->all(), [
                'date_in' => 'required|date',
                'kilometres' => 'required|string',
                'user_id' => 'required|integer',
                'car_id' => 'required|integer',
                'total' => 'required|numeric',
                'phone' => 'required|integer'
            ], [
                'date_in.required' => 'La fecha de ingreso es requerida',
                'kilometres.required' => 'Los kilometros son requeridos',
                'user_id.required' => 'El usuario que generó la orden es requerido',
                'car_id.required' => 'El id del coche es necesario',
                'total.required' => 'El total es requerido y debe ser numerico',
                'phone.required' => 'El telefono es requerido y debe ser numerico'

            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 304);
            }

            $total = $request->total;

            // Redondear el total a dos decimales
            $total = round($total, 2);

            // Convertir el total a formato de coma flotante con punto como separador decimal
            $total = str_replace(',', '.', $total);

            $order = Order::create([
                'date_in' => $request->date_in,
                'kilometres' => $request->kilometres,
                'state' => $request->state,
                'total' => $total,
                'user_id' => $request->user_id,
                'car_id' => $request->car_id,
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'phone' => $request->phone
            ]);

            return response()->json(['message' => 'Post creado con exito', 'data' => $order], 201);
        } else {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción'], 200);
        }
    }

    public function update(Request $request)
    {

        $order = Order::findOrFail($request->id);
        if ($order) {
            $order->update($request->all());
            return response()->json(['order' => $order]);
        } else {
            return response()->json(['No existe esa orden']);
        }
    }

    public function delete(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;

        if ($role_id == 1) {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $order = Order::find($request->order_id);

            if ($order) {
                $order->delete();
                return response()->json(['message' => 'Se encontró la orden', 'La orden que borró fue:' => $order], 200);
            } else {
                return response()->json(['message' => 'Error al encontrar la orden'], 200);
            }
        } else {
            return response()->json(['message' => 'No está autorizado a realizar esta acción'], 200);
        }
    }

    public function getOrdersByPlate(Request $request, $plate){
        $user = Auth::user();
        $role_id = $user->role_id;

        if ($role_id == 1) {
            $car = Car::where("plate",$plate)->first();
            if(!$car){
                return response()->json(["message"=>"No existe ninguna orden asociada a esa matrícula","ok"=>false],404);
            }
            $orders = $car->orders->toArray();
            return response()->json($orders);
        }else {
            return response()->json(['message' => 'No está autorizado a realizar esta acción'], 200);
        }
    }
}
