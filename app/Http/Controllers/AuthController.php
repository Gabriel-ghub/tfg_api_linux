<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }


    public function login(Request $request)
    {
        try {
            DB::connection()->getPDO();
            DB::connection()->getDatabaseName();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error en la conexión a la BBDD',
            ], 500);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Usuario y/o contraseña incorrecta',
                'errors' => $validator->errors()
            ], 400));
        }
        // $request->validate();
        $credentials = $request->only('email', 'password');
        // $token = auth()->claims(['foo' => 'bar'])->attempt($credentials);

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'error' => 'Usuario o contraseña incorrecta',
            ], 401);
        }
        $user = Auth::user();
        $user = [
            'name' => $user['name'],
            'role_id' => $user['role_id']
        ];
        return response()->json([
            'access_token' => $token,
            // 'token_type' => 'bearer',
            // 'expires_in' => env('JWT_TTL') * 3600, //auth()->factory()->getTTL() * 60,
            'user' => $user,
        ]);
    }

    public function verifyToken()
    {
        $validate = Auth::user();
        print_r("LLEGA");
        print_r($validate);
        exit;
        // return response()->json([
        //     "name" => $name,
        //     "role_id" => $role_id
        // ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:70',
            'surname' => 'required|string|max:70',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:10|unique:users',
            'password' => 'required|string|min:6',
        ], [
            'name.required' => 'El nombre es requerido',
            'name.required' => 'El apellido es requerido',
            'email.unique' => 'Ese email ya existe',
            'email.required' => 'Ese email es requerido',
            'username.required' => 'El nombre de usuario es requerido',
            'username.unique' => 'Ese nombre de usuario ya existe',
            'password.required' => 'La contraseña es requerida, y debe tener minimo 6 caracteres',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'username' => $request->username,
            'role_id' => (int)$request->role_id,
            'password' => Hash::make($request->password)
        ]);

        //$token = Auth::login($user);
        return response()->json([
            'message' => "User successfully registered",
            'user' => $user,
        ]);
    }

   

    public function me()
    {
        $user = Auth::user();
        if(!$user){
            return response()->json(["message"=>"Token invalido","ok"=>false],400);
        }
        $data = [
            "name" => $user->name,
            "surname" => $user->surname,
            "id" => $user->id,
            "email" => $user->email 
        ];   
        return response()->json(["user"=>$data,"ok"=>true],200);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'User successfully signed out',
        ]);
    }


    public function refresh()
    {
        $user = Auth::user();
        $data_user = [
            "name" => $user->name,
            "surname" => $user->surname,
            "email" => $user ->email,
            "id" => $user ->id
        ];
        return response()->json([
            'access_token' => Auth::refresh(),
        ]);
    }

    public function createStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:70',
            'surname' => 'required|string|max:70',
            'email' => 'required|string|email|max:255|unique:users',
            'course_id' => 'required',
        ], [
            'name.required' => 'El nombre es requerido',
            'surname.required' => 'El apellido es requerido',
            'email.unique' => 'Ese email ya existe',
            'email.required' => 'Ese email es requerido',
            'course_id' => 'Debe asignar el alumno a un curso',
        ]);
        if ($validator->fails()) {
            $errors = [];
            
            foreach ($validator->errors()->toArray() as $key => $value) {
                $errors[$key] = $value;
            }
            
            $responseData = [
                'errors' => $errors,
                'ok' => false
            ];
            
            return response()->json(
                    $responseData
            , 400);
        }
        


        $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8); // Generamos una contraseña aleatoria de 8 caracteres

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'role_id' => (int)$request->role_id,
            'password' => Hash::make($request->password),
            "course_id" => (int)$request->course_id
        ]);

        //$token = Auth::login($user);
        return response()->json([
            'message' => "User successfully registered",
            'user' => $user->email,
            'password' => $password,
        ]);
    }

    function createStudentsFromCSV(Request $request)
    {
        if (!$request->hasFile('csv')) {
            return response()->json(['message' => 'No se ha enviado ningún archivo.'], 400);
        }

        // Obtenemos el archivo del request
        $file = $request->file('csv');

        // Validamos que el archivo sea un CSV
        if ($file->getClientOriginalExtension() != 'csv') {
            return response()->json(['message' => 'El archivo no es un CSV.'], 400);
        }
        $csvPath = $file->getRealPath();
        $courseId = $request->course_id;
        // Validamos que el archivo sea un CSV
        // if (mime_content_type($csvPath) != 'text/csv') {
        //     return response()->json(['error' => 'El archivo no es un CSV.'], 400);
        // }

        $csv = fopen($csvPath, 'r');
        $header = fgetcsv($csv); // Leemos la primera línea para obtener los nombres de las columnas
        $errors = [];

        while ($data = fgetcsv($csv)) {
            // Asignamos los valores de las columnas a variables con nombres más legibles
            $name = $data[0];
            $surname = $data[1];
            $username = $data[2];
            $email = $data[3];
            $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8); // Generamos una contraseña aleatoria de 8 caracteres

            // Validamos si el email ya existe en la base de datos
            $userExists = User::where('email', $email)->exists();
            if ($userExists) {
                $errors[] = ['email' => $email, 'message' => 'El email ya existe.'];
                continue;
            }

            // Creamos el usuario
            $user = new User;
            $user->name = $name;
            $user->surname = $surname;
            $user->username = $username;
            $user->email = $email;
            $user->role_id = 2;
            $user->course_id = $courseId;
            $user->password = Hash::make($password); // En Laravel, las contraseñas se deben guardar encriptadas
            $user->save();

            // Asociamos al usuario al curso
        }

        fclose($csv);
        if (!empty($errors)) {
            return response()->json(['Failed' => 'Error al ingresar los sigueintes usuarios', 'errors' => $errors], 400);
        }

        return response()->json(['success' => 'Se han insertado los usuarios correctamente.'], 200);
    }

    function getAllTeachers(Request $request)
    {
        $user = Auth::user();
        $role_id = $user->role_id;
        if ($role_id == 1) {
            //get all users from database where role_id = 2
            // $users = User::where('role_id', $role_search)->get();
            $users = User::select('name', 'surname', 'email')->where('role_id', 1)->get();
            return response()->json($users, 200);
        } else {
            return response()->json(['error' => 'No tienes permisos para realizar esta acción.'], 400);
        }
    }
}
