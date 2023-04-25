<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }


    public function index()
    {

        $user = Auth::user();
        $role_id = $user->role_id;
        if ($role_id == 1) {
            $courses = Course::All();
            return response()->json($courses, 200);
        } else {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción'], 200);
        }
    }


    public function show($id)
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        if ($role_id == 1) {
            $course = Course::find($id);

            if (!$course) {
                return response()->json(['error' => 'Curso no encontrado'], 404);
            }

            return response()->json($course);
        } else {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción'], 200);
        }
    }

    public function updateCourse(Request $request, $id)
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        if ($role_id == 1) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'year' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $course = Course::find($id);

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            $course->name = $request->input('name');
            $course->year = $request->input('year');
            $course->save();
            return response()->json(['message' => 'Course updated successfully', 'data' => $course]);
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
                'name' => 'required|string',
                'year' => 'required|integer',
            ], [
                'name.required' => 'El nombre es requerido',
                'year.required' => 'El año es requerido',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 304);
            }
            $course = Course::create([
                "name" => $request->name,
                "year" => $request->year,
            ]);
            return response()->json($course, 200);
        } else {
            return response()->json(['message' => 'No tiene permisos para realizar esta acción'], 200);
        }
    }

    public function delete(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;

        if ($role_id == 1) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $course = Course::find($request->id);

            if ($course) {
                $course->delete();
                return response()->json(['message' => 'El curso se borró correctamente', 'El curso que borró fue:' => $course], 200);
            } else {
                return response()->json(['message' => 'Error al encontrar el curso'], 200);
            }
        } else {
            return response()->json(['message' => 'No está autorizado a realizar esta acción'], 200);
        }
    }

    public function getStudents($id)
    {
        $user = Auth::user();
        $role_id = $user->role_id;

        if ($role_id == 1) {

            try {
                // Busca el curso con el ID proporcionado
                $curso = Course::findOrFail($id);

                // Obtiene una lista de los alumnos asociados al curso
                $alumnos = $curso->users()->select('id', 'name', 'surname', 'email',
                    'username'
                )->get()->toArray();

                
                // Devuelve la lista de alumnos en formato JSON
                return response()->json($alumnos, 200);
            } catch (\Exception $e) {
                // En caso de error, devuelve una respuesta de error con el mensaje del error
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['message' => 'No está autorizado a realizar esta acción'], 200);
        }
    }
}
