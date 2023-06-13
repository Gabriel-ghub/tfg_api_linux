<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\User;
use App\Models\Work;
use App\Models\Order;
use App\Models\Anomaly;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        try {
            $orders = Order::select('orders.id', 'cars.plate', 'orders.date_in', 'orders.kilometres', 'orders.state', 'orders.name', 'orders.surname', 'orders.email', 'orders.phone')
                ->join('cars', 'cars.id', '=', 'orders.car_id')
                ->orderBy('orders.id', 'desc') // Ordenar por ID de orden en orden descendente
                ->get();

            return response()->json($orders, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las ordenes.'], 500);
        }
    }

    public function getOrder($orderNumber)
    {
        $order = Order::where('id', $orderNumber)->first();
        $order->anomalies;
        $plate = Car::where("id", "=", $order->car_id)->pluck('plate')->first();

        if ($order) {
            return response()->json([
                'success' => true,
                'order' => $order,
                'plate' => $plate
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
            'date_in' => 'bail|required|date',
            'kilometres' => 'bail|required|integer',
            'car_id' => 'bail|required|integer|exists:cars,id',
            'name' => 'bail|required|string|max:40',
            'surname' => 'bail|required|string|max:40',
            'phone' => 'bail|nullable|string|max:12|regex:/^[0-9]+$/',
            'email' => 'bail|nullable|string|max:50|email'
        ], [
            'date_in.required' => 'La fecha de ingreso es requerida.',
            'date_in.date' => 'El formato de la fecha debe ser dd-mm-aaaa.',
            'car_id.required' => 'El ID del coche es necesario.',
            'car_id.exists' => 'El coche no existe.',
            'kilometres.required' => 'Los kilómetros son requeridos.',
            'kilometres.integer' => 'Los kilómetros deben ser números.',
            'phone.max' => 'Máximo 12 caracteres.',
            'phone.string' => 'El teléfono solo acepta números, sin espacios.',
            'phone.regex' => 'El teléfono debe contener solo números.',
            'name.required' => 'El nombre es requerido.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'Máximo 40 caracteres.',
            'surname.required' => 'El apellido es requerido.',
            'surname.string' => 'El apellido debe ser una cadena de texto.',
            'surname.max' => 'Máximo 40 caracteres.',
            'email.string' => 'El correo electrónico debe ser una cadena de texto.',
            'email.max' => 'Máximo 50 caracteres.',
            'email.email' => 'El correo electrónico debe tener un formato válido.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
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
            'state' => false,
            'total' => 0,
            'creator_user_id' => $user_id,
            'car_id' => $request->car_id,
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email ?? '',
            'phone' => $request->phone ?? ''
        ]);

        return response()->json(['message' => 'Post creado con éxito', 'data' => $order], 201);
    }




    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['bail', 'required', 'string', 'regex:/^[a-zA-Z\s]+$/u', 'min:3', 'max:40'],
            'surname' => ['bail', 'required', 'string', 'regex:/^[a-zA-Z\s]+$/u', 'min:3', 'max:40'],
            'phone' => ['bail', 'nullable', 'string', 'max:12', 'regex:/^[0-9]+$/'],
            'email' => ['bail', 'nullable', 'string', 'max:50', 'email'],
            'kilometres' => ['bail', 'required', 'integer'],
        ], [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.string' => 'El campo nombre debe ser una cadena de caracteres.',
            'name.regex' => 'El campo nombre solo puede contener letras y espacios en blanco.',
            'name.min' => 'El campo nombre debe tener al menos 3 caracteres.',
            'name.max' => 'El campo nombre no puede tener más de 40 caracteres.',
            'surname.required' => 'El campo apellido es obligatorio.',
            'surname.string' => 'El campo apellido debe ser una cadena de caracteres.',
            'surname.regex' => 'El campo apellido solo puede contener letras y espacios en blanco.',
            'surname.min' => 'El campo apellido debe tener al menos 3 caracteres.',
            'surname.max' => 'El campo apellido no puede tener más de 40 caracteres.',
            'kilometres.required' => 'Los kilómetros son requeridos.',
            'kilometres.integer' => 'Los kilómetros deben ser números.',
            'phone.max' => 'Máximo 12 caracteres.',
            'phone.string' => 'El teléfono debe ser una cadena de texto.',
            'phone.regex' => 'El teléfono debe contener solo números.',
            'email.max' => 'Máximo 50 caracteres.',
            'email.string' => 'El correo electrónico debe ser una cadena de texto.',
            'email.email' => 'El correo electrónico debe tener un formato válido.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 409);
        }

        $order = Order::findOrFail($request->id);

        if ($order) {
            $order->name = trim(preg_replace('/\s+/', ' ', $request->name));
            $order->surname = trim(preg_replace('/\s+/', ' ', $request->surname));
            $order->phone = trim($request->phone) ?? '';
            $order->email = trim($request->email) ?? '';
            $order->kilometres = $request->kilometres;
            $order->save();

            return response()->json(['order' => $order]);
        } else {
            return response()->json(["message" => 'No existe esa orden'], 404);
        }
    }



    public function delete($order_id)
    {
        $validator = Validator::make(['order_id' => $order_id], [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $order = Order::find($order_id);

        if ($order) {
            $order->delete();
            return response()->json(['message' => 'Se encontró la orden', 'La orden que borró fue:' => $order], 200);
        } else {
            return response()->json(['message' => 'Error al encontrar la orden'], 200);
        }
    }

    public function getOrdersByPlate(Request $request, $plate)
    {
        $validator = Validator::make(['plate' => $request->plate], [
            'plate' => 'required|string|regex:/^[a-zA-Z0-9]+$/|max:11',
        ], [
            'plate.required' => 'La mtricula es requerida',
            'plate.string' => 'La matrícula debe ser solo texto',
            'plate.regex' => 'La matrícula solo puede contener números y letras',
            'plate.max' => 'Máximo 11 carácteres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }
        $car = Car::where("plate", $plate)->first();
        if (!$car) {
            return response()->json(["message" => "No existe ninguna orden asociada a esa matrícula", "ok" => false], 404);
        }
        $orders = Order::select('orders.id', 'cars.plate', 'orders.date_in', 'orders.kilometres', 'orders.state', 'orders.name', 'orders.surname', 'orders.email', 'orders.phone',)
            ->join('cars', 'cars.id', '=', 'orders.car_id')
            ->where("cars.plate", "=", "$plate")
            ->get();
        return response()->json($orders);
    }

    public function closeOrder(Request $request, $order_id)
    {
        $validator = Validator::make(['order_id' => $order_id], [
            'order_id' => 'required|exists:orders,id',
        ], [
            'order_id.required' => 'El ID de orden es requerido.',
            'order_id.exists' => 'El ID de orden no existe en la tabla orders.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $order = Order::find($order_id);
        $order->state = 1;
        $order->date_out = Carbon::now()->toDateString();
        $order->save();

        return response()->json(["message" => "Orden cerrada correctamente, se han cerrado todos sus trabajos"], 200);
    }

    public function getStudents(Request $request, $order_id)
    {
        $order = Order::find($order_id);
        if ($order) {
            $assignedUsers = $order->assignedUsers()
                ->join('courses', 'users.course_id', '=', 'courses.id')
                ->select('users.id', 'users.name', 'users.surname', 'users.email', 'courses.name as course_name', 'courses.year')
                ->get()
                ->toArray();
            return response()->json($assignedUsers, 200);
        } else {
            return response()->json(["message" => "No se ha encontrado ninguna orden con ese ID"], 404);
        }
    }

    public function getUsersFromCourseAndOrder(Request $request, $order_id, $course_id)
    {
        $users_assigned = User::select('users.id', 'users.name', 'users.surname', 'users.email')
            ->join('order_user', 'users.id', '=', 'order_user.user_id')
            ->where('order_id', $order_id)
            ->where('users.course_id', $course_id)
            ->where('users.role_id', 2)
            ->get();
        $users_not_assigned = User::select('users.id', 'users.name', 'users.surname', 'users.email')
            ->where('users.course_id', $course_id)
            ->where('users.role_id', 2)
            ->whereDoesntHave('assignedOrders', function ($query) use ($order_id) {
                $query->where('order_id', $order_id);
            })
            ->get();
        $response = [
            "ASSIGNED" => $users_assigned,
            "NOT_ASSIGNED" => $users_not_assigned
        ];
        return response()->json($response, 200);
    }

    public function associate(Request $request)
    {
        $user_ids = $request->user_ids;
        $order = Order::find($request->order_id);
        foreach ($user_ids as $user) {
            $user = User::find($user);
            if ($user) {
                $order->assignedUsers()->attach($user);
            } else {
                return response()->json(["message" => "Error, usuario o trabajo no encontrado", "ok" => "false"], 404);
            }
        }
        return response()->json(["message" => "Creado correctamente"], 200);
    }

    public function disassociate(Request $request)
    {
        $order = Order::find($request->order_id);
        $user = User::find($request->user_id);
        $response = $order->assignedUsers()->detach($user->id);
        if ($response) {
            return response()->json(["message" => "Dettach corrrecto"], 200);
        } else {
            return response()->json(["message" => "Fallo", "ok" => "false"], 400);
        }
    }

    public function getOrdersFormStudent(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;

        $orders = Order::select('orders.id', 'cars.plate', 'orders.state')
            ->join('cars', 'orders.car_id', '=', 'cars.id')
            ->join('order_user', 'order_user.order_id', '=', 'orders.id')
            ->whereIn('orders.state', [0, 1])
            ->where('order_user.user_id', '=', $user_id)
            ->with('materials') // Se agrega la relación de materiales
            ->with('works') // Se agrega la relación de trabajo
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No se encontraron órdenes asociadas al usuario'], 404);
        }

        list($finishedOrders, $pendingOrders) = $orders->partition(function ($order) {
            return $order->state == 1;
        });

        $finishedResults = [];
        foreach ($finishedOrders as $order) {
            $anomalies = Anomaly::select('description')
                ->where('order_id', $order->id)
                ->get()
                ->pluck('description');

            $finishedResults[] = [
                'id' => $order->id,
                'plate' => $order->plate,
                'anomalies' => $anomalies->toArray(),
                'materials' => $order->materials->pluck('description')->toArray(), // Se agrega la lista de materiales
                'work_description' => $order->work ? $order->work->description : null // Se agrega la descripción del trabajo
            ];
        }

        $pendingResults = [];
        foreach ($pendingOrders as $order) {
            $anomalies = Anomaly::select('description')
                ->where('order_id', $order->id)
                ->get()
                ->pluck('description');

            $pendingResults[] = [
                'id' => $order->id,
                'plate' => $order->plate,
                'anomalies' => $anomalies->toArray(),
                'materials' => $order->materials->pluck('description')->toArray(), // Se agrega la lista de materiales
                'work_description' => $order->work ? $order->work->description : null // Se agrega la descripción del trabajo
            ];
        }

        return response()->json([
            'finished' => $finishedResults,
            'pending' => $pendingResults
        ]);
    }

    public function updateMaterialsAndWork(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'description' => 'required',
            'materials' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $user_id = $user->id;
        $order_id = $request->order_id;
        $description =  $request->description;
        $materials = $request->materials;

        $work = Work::create([
            'order_id' => $order_id,
            'user_id' => $user_id,
            'description' => $description
        ]);

        if (!$work) {
            return response()->json(['message' => 'Error al crear el trabajo'], 500);
        }

        foreach ($materials as $material) {
            $newMaterial = Material::create([
                'order_id' => $order_id,
                'description' => $material
            ]);

            if (!$newMaterial) {
                return response()->json(['message' => 'Error al crear el material'], 500);
            }
        }

        return response()->json(['message' => 'Trabajo y materiales creados exitosamente'], 200);
    }

    public function getWorksAndMaterials(Request $request, $order_id)
    {
        $user = Auth::user();


        $works = Work::select('id', 'description')
            ->where('order_id', $order_id)
            ->get();
        if ($user->roleid == 1) {
            $materials = Material::select('id', 'description', 'quantity')
                ->where('order_id', $order_id)
                ->get();
        } else {
            $materials = Material::select('id', 'description', 'quantity', 'price')
                ->where('order_id', $order_id)
                ->get();
        }

        return response()->json([
            'works' => $works,
            'materials' => $materials
        ]);
    }

    public function getDataToPDF(Request $request, $order_id)
    {
        $order = Order::select('date_in', 'date_out', 'kilometres', 'name', 'surname', 'phone', 'email', 'car_id')->find($order_id);
        $anomalies = Anomaly::select('description')
            ->where('order_id', $order_id)
            ->get()
            ->pluck('description');
        $materials = Material::select('description', "price", "quantity")->where('order_id', $order_id)->get()->toArray();
        $work = Work::select('description')->where('order_id', $order_id)->get()->pluck('description');
        $car = Car::select("brand", "model", "plate")->where('id', $order->car_id)->get()->toArray();
        //get users asssiagned to order
        $students = Order::find($order_id)->assignedUsers()->get()->map(function ($user) {
            return $user->name . ' ' . $user->surname;
        });

        return response()->json([
            'order' => $order,
            'anomalies' => $anomalies,
            'materials' => $materials,
            'works' => $work,
            'car' => $car,
            'students' => $students
        ]);
    }

    public function prueba(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_in' => 'bail|required|date',
            'kilometres' => 'bail|required|integer',
            'car_id' => 'bail|required|integer|exists:cars,id',
            'name' => 'bail|required|string|max:40',
            'surname' => 'bail|required|string|max:40',
            'phone' => 'bail|nullable|string|max:12|regex:/^[0-9]+$/',
            'email' => 'bail|nullable|string|max:50|email'
        ], [
            'date_in.required' => 'La fecha de ingreso es requerida.',
            'date_in.date' => 'El formato de la fecha debe ser dd-mm-aaaa.',
            'car_id.required' => 'El ID del coche es necesario.',
            'car_id.exists' => 'El coche no existe.',
            'kilometres.required' => 'Los kilómetros son requeridos.',
            'kilometres.integer' => 'Los kilómetros deben ser números.',
            'phone.max' => 'Máximo 12 caracteres.',
            'phone.string' => 'El teléfono solo acepta números, sin espacios.',
            'phone.regex' => 'El teléfono debe contener solo números.',
            'name.required' => 'El nombre es requerido.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'Máximo 40 caracteres.',
            'surname.required' => 'El apellido es requerido.',
            'surname.string' => 'El apellido debe ser una cadena de texto.',
            'surname.max' => 'Máximo 40 caracteres.',
            'email.string' => 'El correo electrónico debe ser una cadena de texto.',
            'email.max' => 'Máximo 50 caracteres.',
            'email.email' => 'El correo electrónico debe tener un formato válido.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 409);
        }
    }
}
