<?php

namespace App\Http\Controllers;

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
                'description' => 'required|string',
                'order_id' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $work = Work::create([
                'description' => $request->description,
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
                'description' => 'required|string',
                'id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
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
            // $works = Work::select('description', 'state', 'id')->where('order_id', $order_id)->get()->toArray();
            return response()->json($returnData, 200);
    }

    //delete work
    public function delete(Request $request, $id)
    {
 
            $order_id = $request->order_id;
            $work = Work::find($id);
            $work->delete();
            $returnData = [
                'id' => $work->id,
                'description' => $work->description,    
                'order' => $work->order_id,
            ];
            // $works = Work::select('description', 'state', 'id')->where('order_id', $order_id)->get()->toArray();
            return response()->json($returnData, 200);
    }


    public function getWorksByOrderId(Request $request, $order_id)
    {
            $works = Work::select('description', 'state', 'id')->where('order_id', $order_id)->get()->toArray();
            return response()->json($works, 200);
    }



    public function associate(Request $request)
    {

        $user_id = $request->user_id;
        $work = Work::find($request->work_id);
        $user = User::find($user_id);
            if ($user) {
                $work->users()->attach($user_id);
                return response()->json(["message" => "Creado correctamente"],200);
            }else{
                return response()->json(["message" => "Error, usuario o trabajo no encontrado","ok"=>"false"],404);
            }
    }

    public function disassociate(Request $request)
    {
        $work = Work::find($request->work_id);
        $user = User::find($request->user_id);
            $response = $work->users()->detach($user->id);
            if($response){
                return response()->json(["message"=>"Dettach corrrecto"], 200);
            }else{
                return response()->json(["message" => "Fallo", "ok"=>"false"], 400);
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

    public function getUsersFromCourseAndWork(Request $request, $work_id, $course_id){
            $users_assigned = User::select('users.id', 'users.name', 'users.surname','users.email')
            ->join('user_work', 'users.id', '=', 'user_work.user_id')
            ->join('works', 'user_work.work_id', '=', 'works.id')
            ->where('works.id', $work_id)
            ->where('users.course_id', $course_id)
            ->get();

            $users_not_assigned = User::select('users.id', 'users.name', 'users.surname','users.email')
            ->whereNotIn('id', function ($query) use ($work_id) {
                $query->select('user_id')
                      ->from('user_work')
                      ->join('users', 'user_work.user_id', '=', 'users.id')
                      ->where('user_work.work_id',$work_id);
            })
            ->where('course_id', $course_id)
            ->get()->toArray();
            $response = [
                "ASSIGNED" =>$users_assigned,
                "NOT_ASSIGNED" =>$users_not_assigned
            ];
            return response()->json($response ,200);
    }

    public function getStudents(Request $request,$id){
            $work = Work::find($id);
            $students_associated = $work->users()->select('name','surname','email','id')->get()->toArray(); 
            return response()->json($students_associated, 200);
    }

    public function getWorksByStudent(){
        $user = Auth::user();
        $user_id = $user->id;
            $pendingWorks = Work::select('works.id','works.description','cars.plate','works.state','user_work.description as comment')
            ->join('user_work','works.id','=','user_work.work_id')
            ->join('orders','works.order_id','=','orders.id')
            ->join('cars','orders.car_id','=','cars.id')
            ->where('user_work.user_id',"=","$user_id")
            ->where('works.state', '=','0')
            ->get()->toArray();
            $completedWorks = Work::select('works.id','works.description','cars.plate','works.state','user_work.description as comment')
            ->join('user_work','works.id','=','user_work.work_id')
            ->join('orders','works.order_id','=','orders.id')
            ->join('cars','orders.car_id','=','cars.id')
            ->where('user_work.user_id',"=","$user_id")
            ->where('works.state', '=','1')
            ->get()->toArray();
            return response()->json([
                                    "pendientes" => $pendingWorks,
                                    "finalizados" =>$completedWorks
                                    ]);
    }

    public function changeState(Request $request){
        $user = Auth::user();
        $user_id = $user->id;
            $work = Work::find($request->work_id);
            if ($work) {
                if($request->state && $user->role_id ==1){
                    $work->state = $request->state;
                }else{
                    $work->state = 1;
                    $work->description = $request->description;
                }
                $work->save();
                return response()->json(["Trabajo actualizado" => $work]);
            }else{
                return response()->json(["message" => "El trabajo no existe"]);
            }

    }
}