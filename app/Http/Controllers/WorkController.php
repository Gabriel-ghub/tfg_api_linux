<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WorkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }



    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'bail|required|string|max:200',
            'order_id' => 'bail|required|exists:orders,id',
        ], [
            'description.required' => 'La descripción es requerida.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no puede tener más de 200 caracteres.',
            'order_id.required' => 'El ID de orden es requerido.',
            'order_id.exists' => 'El ID de orden no existe en la tabla orders.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }

        $sanitizedDescription = strip_tags($request->description);

        $work = Work::create([
            'description' => $sanitizedDescription,
            'hours' => "0",
            'state' => false,
            'order_id' => $request->order_id,
        ]);

        $returnData = [
            'id' => $work->id,
            'description' => $work->description,
            'order' => $work->order_id,
        ];

        return response()->json($returnData, 200);
    }


    //update work
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'bail|required|string|min:2|max:200',
            'id' => 'bail|required|exists:works,id',
        ], [
            'description.required' => 'El campo descripción es obligatorio.',
            'description.string' => 'El campo descripción debe ser una cadena de caracteres.',
            'description.min' => 'El campo descripción debe tener al menos :min caracteres.',
            'description.max' => 'El campo descripción no debe tener más de :max caracteres.',
            'id.required' => 'El campo ID es obligatorio.',
            'id.exists' => 'El ID especificado no existe',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }
        $order_id = $request->order_id;
        $work = Work::find($request->id);
        $work->description = $request->description;
        $work->save();
        $returnData = [
            'id' => $work->id,
            'description' => $work->description,
            'order' => $work->order_id,
        ];
        return response()->json($returnData, 200);
    }

    //delete work
    public function delete(Request $request, $id)
    {

        $work = Work::find($id);

        if (!$work) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => ['id' => 'El trabajo no existe']
            ], 409);
        }

        $work->delete();

        $returnData = [
            'id' => $work->id,
            'description' => $work->description,
            'order' => $work->order_id,
        ];

        return response()->json($returnData, 200);
    }

    public function deleteWork(Request $request, $id)
    {
        // Validar que el ID del trabajo exista
        $validator = Validator::make(
            ['id' => $id],
            ['id' => 'required|exists:works,id'],
            ['id.exists' => 'El trabajo no existe.']
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 409);
        }

        // Realizar la lógica de eliminación del trabajo
        $trabajo = Work::find($id);
        $trabajo->delete();

        // Retornar una respuesta exitosa
        return response()->json(['message' => 'Trabajo eliminado correctamente']);
    }


    public function getWorksByOrderId(Request $request, $order_id)
    {
        $order = Order::find($order_id);
        // $works = Work::select('description','id')->where('order_id', $order_id)->get()->toArray();
        $works = $order->works;
        return response()->json($works, 200);

    }



    public function associate(Request $request)
    {
        $user_ids = $request->user_ids;
        $work = Work::find($request->work_id);
        foreach ($user_ids as $user_id) {
            $user = User::find($user_id);
            if ($user) {
                $work->users()->attach($user_id);
            } else {
                return response()->json(["message" => "Error, usuario o trabajo no encontrado", "ok" => "false"], 404);
            }
        }
        return response()->json(["message" => "Creado correctamente"], 200);
    }

    public function disassociate(Request $request)
    {
        $work = Work::find($request->work_id);
        $user = User::find($request->user_id);
        $response = $work->users()->detach($user->id);
        if ($response) {
            return response()->json(["message" => "Dettach corrrecto"], 200);
        } else {
            return response()->json(["message" => "Fallo", "ok" => "false"], 400);
        }
    }

    public function getUsersByWorkId(Request $request)
    {
        $work = Work::find($request->work_id);
        $users = $work->users()->get();
        foreach ($users as $user) {
        }
        exit();
        return response()->json(['message' => 'No tiene permisos para realizar esta acción', 'usuarios asignados al trabajo' => $work], 200);
    }

    public function getUsersFromCourseAndWork(Request $request, $work_id, $course_id)
    {
        $users_assigned = User::select('users.id', 'users.name', 'users.surname', 'users.email')
            ->join('user_work', 'users.id', '=', 'user_work.user_id')
            ->join('works', 'user_work.work_id', '=', 'works.id')
            ->where('works.id', $work_id)
            ->where('users.course_id', $course_id)
            ->get();

        $users_not_assigned = User::select('users.id', 'users.name', 'users.surname', 'users.email')
            ->whereNotIn('id', function ($query) use ($work_id) {
                $query->select('user_id')
                    ->from('user_work')
                    ->join('users', 'user_work.user_id', '=', 'users.id')
                    ->where('user_work.work_id', $work_id);
            })
            ->where('course_id', $course_id)
            ->get()->toArray();
        $response = [
            "ASSIGNED" => $users_assigned,
            "NOT_ASSIGNED" => $users_not_assigned
        ];
        return response()->json($response, 200);
    }

    public function getStudents(Request $request, $id)
    {
        $work = Work::find($id);
        $students_associated = $work->users()
            ->withPivot('description')
            ->join('courses', 'users.course_id', '=', 'courses.id')
            ->select('users.name', 'users.surname', 'users.email', 'users.id', 'courses.name as course', 'courses.year as year')
            ->get()
            ->toArray();
        return response()->json($students_associated, 200);
    }


    public function getWorksByStudent()
    {
        $user = Auth::user();
        $user_id = $user->id;
        $pendingWorks = Work::select('works.id', 'works.description', 'cars.plate', 'works.state', 'user_work.description as comment')
            ->join('user_work', 'works.id', '=', 'user_work.work_id')
            ->join('orders', 'works.order_id', '=', 'orders.id')
            ->join('cars', 'orders.car_id', '=', 'cars.id')
            ->where('user_work.user_id', "=", "$user_id")
            ->where('works.state', '=', '0')
            ->get()->toArray();
        $completedWorks = Work::select('works.id', 'works.description', 'cars.plate', 'works.state', 'user_work.description as comment')
            ->join('user_work', 'works.id', '=', 'user_work.work_id')
            ->join('orders', 'works.order_id', '=', 'orders.id')
            ->join('cars', 'orders.car_id', '=', 'cars.id')
            ->where('user_work.user_id', "=", "$user_id")
            ->where('works.state', '=', '1')
            ->get()->toArray();
        return response()->json([
            "pendientes" => $pendingWorks,
            "finalizados" => $completedWorks
        ]);
    }

    public function changeState(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $work = Work::find($request->work_id);
        if ($work) {
            if ($request->state && $user->role_id == 1) {
                $work->state = $request->state;
            } else {
                $work->state = 1;
                $work->description = $request->description;
            }
            $work->save();
            return response()->json(["Trabajo actualizado" => $work]);
        } else {
            return response()->json(["message" => "El trabajo no existe"]);
        }
    }

    public function changeState2(Request $request)
    {
        $user = Auth::user();
        $user_id = $user->id;
        $validator = Validator::make($request->all(), [
            'work_id' => 'required|exists:works,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }
        $work = Work::find($request->work_id);
        if (!$work) {
            return response()->json(["message" => "El trabajo no existe"]);
        }
        if ($user->role_id == 1) {
            $validator = Validator::make($request->all(), [
                'state' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 409);
            }
            $work->state = $request->state;
            $work->save();
        } else if ($user->role_id == 2) {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string|min:1|max:150',
            ], [
                'description.required' => 'La descripción es requerida',
                'description.string' => 'La descripción debe ser un texto',
                'description.min' => 'La descripción debe tener al menos :min caracteres',
                'description.max' => 'La descripción no debe tener más de :max caracteres',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 409);
            }

            $user = User::find($user_id);
            $work_id = Work::find($work->id);

            $changed_description = $user->works()->updateExistingPivot($work_id, ['description' => $request->description]);
            if ($changed_description) {
                $work->state = 1;
                $work->save();
                return response()->json(["Trabajo actualizado" => $work], 200);
            } else {
                return response()->json(["message" => "No se pudo actualizar la descripción"], 409);
            }
        }
        return response()->json(["Trabajo actualizado" => $work]);
    }

    public function getWorkDetails(Request $request, $id)
    {
        $work = Work::find($id);
        if ($work) {
            return response()->json($work, 200);
        } else {
            return response()->json(["message" => "No se encontró ningún trabajo con ese id"], 409);
        }
    }

    public function createByStudent(Request $request){
        $user = Auth::user();
        $user_id = $user->id;
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'order_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $work = Work::create([
            'description' => $request->description,
            'order_id' => $request->order_id,
            'user_id' => $user_id,
        ]);

        $works = Order::find($request->order_id)->works;

        return response()->json($works, 200);
    }
}
