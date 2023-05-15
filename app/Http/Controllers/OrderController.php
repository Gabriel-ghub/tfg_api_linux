<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    public function showAll()
    {
            $orders = Order::select('orders.id', 'cars.plate','orders.date_in','orders.kilometres','orders.state','orders.name','orders.surname','orders.email', 'orders.phone',)
                        ->join('cars', 'cars.id', '=', 'orders.car_id')
                        ->get();
            return response()->json($orders, 200);
    }

    public function getOrder($orderNumber)
    {
            $order = Order::where('id', $orderNumber)->first();
            $order->anomalies;
            $plate = Car::where("id","=", $order->car_id)->pluck('plate')->first();

            if ($order) {
                return response()->json([
                    'success' => true,
                    'order' => $order,
                    'plate'=>$plate
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ]);
            }
    }

    public function create(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'date_in' => 'required|date',
                'kilometres' => 'required|integer',
                'car_id' => 'required|integer',
                // 'total' => 'required|numeric',
                'phone' => 'required|integer',
                'name' => 'required|string|max:40',
                'surname' => 'required|string|max:40',
                'email' => 'required|string|max:50'
            ], [
                'date_in.required' => 'La fecha de ingreso es requerida',
                'date_in.date' => 'El formato de la fecha debe ser dd-mm-aaaa',
                'car_id.required' => 'El id del coche es necesario',
                // 'total.required' => 'El total es requerido y debe ser numerico',
                // 'total.numeric' => 'El total debe ser formato numerico',
                'kilometres.required' => 'Los kilometros son requeridos',
                'kilometres.integer' => 'Los kilometros deben ser números',
                'phone.required' => 'El telefono es requerido',
                'phone.integer' => 'El telefono debe ser numerico',
                'name.required' => 'El nombre es requerido',
                'name.string' => 'El nombre debe ser una cadena de texto',
                'name.max' => 'Máximo 40 caracteres',
                'surname.required' => 'El apellido es requerido',
                'surname.string' => 'El apellido debe ser una cadena de texto',
                'surname.max' => 'Máximo 40 caracteres',
                'email.required' => 'El email es requerido',
                'email.string' => 'El email debe ser una cadena de texto',
                'email.max' => 'Máximo 50 caracteres',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 409);
            }

            $total = $request->total;

            // Redondear el total a dos decimales
            $total = round($total, 2);

            // Convertir el total a formato de coma flotante con punto como separador decimal
            $total = str_replace(',', '.', $total);
            $user = Auth::user();
            $user_id = $user->id;

            $order = Order::create([
                'date_in' => $request->date_in,
                'kilometres' => $request->kilometres,
                'state' => "En proceso",
                'total' => 0,
                'user_id' => $user_id,
                'car_id' => $request->car_id,
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'phone' => $request->phone
            ]);
            return response()->json(['message' => 'Post creado con exito', 'data' => $order], 201);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'regex:/^[a-zA-Z]+$/u', 'min:3', 'max:40'],
            'surname' => ['required', 'string', 'regex:/^[a-zA-Z]+$/u', 'min:3', 'max:40'],
            'email' => ['required', 'string', 'email', 'unique:users,email', 'max:50'],
        ], [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.string' => 'El campo nombre debe ser una cadena de caracteres.',
            'name.regex' => 'El campo nombre solo puede contener letras.',
            'name.min' => 'El campo nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El campo nombre no puede tener más de 40 caracteres.',
            'surname.required' => 'El campo apellido es obligatorio.',
            'surname.string' => 'El campo apellido debe ser una cadena de caracteres.',
            'surname.regex' => 'El campo apellido solo puede contener letras.',
            'surname.min' => 'El campo apellido debe tener al menos 3 caracteres.',
            'surname.max' => 'El campo apellido no puede tener más de 40 caracteres.',
            'email.required' => 'El campo correo electrónico es obligatorio.',
            'email.string' => 'El campo correo electrónico debe ser una cadena de caracteres.',
            'email.email' => 'El campo correo electrónico debe ser una dirección de correo válida.',
            'email.unique' => 'Este correo electrónico ya está en uso por otro usuario.',
            'email.max' => 'El campo correo electrónico no puede tener más de 50 caracteres.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }



        $order = Order::findOrFail($request->id);
        if ($order) {
            $order->name = $request->name;
            $order->surname = $request->surname;
            $order->phone = $request->phone;
            $order->email = $request->email;
            $order->kilometres = $request->kilometres;
            $order->save();
            return response()->json(['order' => $order]);
        } else {
            return response()->json(["message"=>'No existe esa orden'], 404);
        }
    }

    public function delete(Request $request)
    {
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
    }

    public function getOrdersByPlate(Request $request,$plate){
        $validator = Validator::make(['plate' => $request->plate], [
            'plate' => 'required|string|regex:/^[a-zA-Z0-9]+$/|max:11',
        ],[
            'plate.required' => 'La mtricula es requerida',
            'plate.string' =>'La matrícula debe ser solo texto',
            'plate.regex' =>'La matrícula solo puede contener números y letras',
            'plate.max' =>'Máximo 11 carácteres',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }
            $car = Car::where("plate",$plate)->first();
            if(!$car){
                return response()->json(["message"=>"No existe ninguna orden asociada a esa matrícula","ok"=>false],404);
            }
            $orders = Order::select('orders.id', 'cars.plate','orders.date_in','orders.kilometres','orders.state','orders.name','orders.surname','orders.email', 'orders.phone',)
            ->join('cars', 'cars.id', '=', 'orders.car_id')
            ->where("cars.plate","=","$plate")
            ->get();
            return response()->json($orders);
    }

    public function closeOrder(Request $request, $order_id){
        $order = Order::find($order_id);
        $works = $order->works;
        if($works){
            foreach ($works as $work) {
                $work->state = true;
                $work->save();
            }
        }
        $order->state= "Finalizada";
        $order->save(); 
        return response()->json(["message"=>"Orden cerrada correctamente, se han cerrado todos sus trabajos"],200);
    }
}
