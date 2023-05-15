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
            'password' => 'required|string|min:6',
        ], [
            'name.required' => 'El nombre es requerido',
            'name.required' => 'El apellido es requerido',
            'email.unique' => 'Ese email ya existe',
            'email.required' => 'Ese email es requerido',
            'password.required' => 'La contraseña es requerida, y debe tener minimo 6 caracteres',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
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
            'name' => 'required|string|regex:/^[a-zA-Z\s]+$/|min:3|max:40',
            'surname' => 'required|string|regex:/^[a-zA-Z\s]+$/|min:3|max:40',
            'email' => 'required|string|email|max:50|unique:users|min:3|max:40',
            'course_id' => 'required|exists:courses,id',
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
            'email.required' => 'El campo correo electrónico es obligatorio.',
            'email.string' => 'El campo correo electrónico debe ser una cadena de caracteres.',
            'email.email' => 'El campo correo electrónico debe ser una dirección de correo válida.',
            'email.min' => 'El campo email debe tener al menos 3 caracteres.',
            'email.max' => 'El campo correo electrónico no puede tener más de 50 caracteres.',
            'email.unique' => 'Este correo electrónico ya está en uso por otro usuario.',
            'course_id.required' => 'El campo ID de curso es obligatorio.',
            'course_id.exists' => 'El ID de curso proporcionado no existe.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }
        
        // $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8); // Generamos una contraseña aleatoria de 8 caracteres
        $password = 123456; // Generamos una contraseña aleatoria de 8 caracteres

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'role_id' => (int)$request->role_id,
            'password' => Hash::make($password),
            "course_id" => (int)$request->course_id
        ]);

        //$token = Auth::login($user);
        return response()->json([
            'message' => "User successfully registered",
            'user' => $user->email,
            'password' => $password,
        ]);
    }


    function getAllTeachers(Request $request)
    {
            //get all users from database where role_id = 2
            // $users = User::where('role_id', $role_search)->get();
            $users = User::select('name', 'surname', 'email')->where('role_id', 1)->get();
            return response()->json($users, 200);
    }



    public function createTeacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|regex:/^[a-zA-Z\s]+$/|min:3|max:40',
            'surname' => 'required|string|regex:/^[a-zA-Z\s]+$/|min:3|max:40',
            'email' => 'required|string|email|max:50|unique:users|min:3|max:40',
            'password' => 'required|string|min:6|max:16',
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
            'email.required' => 'El campo correo electrónico es obligatorio.',
            'email.string' => 'El campo correo electrónico debe ser una cadena de caracteres.',
            'email.email' => 'El campo correo electrónico debe ser una dirección de correo válida.',
            'email.min' => 'El campo email debe tener al menos 3 caracteres.',
            'email.max' => 'El campo correo electrónico no puede tener más de 50 caracteres.',
            'email.unique' => 'Este correo electrónico ya está en uso por otro usuario.',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.max' => 'La contraseña no puede tener más de 15 caracteres.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }
        
        $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8); // Generamos una contraseña aleatoria de 8 caracteres

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'role_id' => 1,
            'password' => Hash::make($request->password)
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
        $file = $request->file('csv');
        if ($file->getClientOriginalExtension() != 'csv') {
            return response()->json(['message' => 'El archivo no es un CSV.'], 400);
        }
        $csvPath = $file->getRealPath();
        $courseId = $request->course_id;

        $csv = fopen($csvPath, 'r');
        $header = fgetcsv($csv); 
        $errors = [];
        $students = [];
        while ($data = fgetcsv($csv)) {
         
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
            return response()->json(['message' => 'Error al ingresar los sigueintes usuarios', 'errors' => $errors], 400);
        }

        return response()->json(['success' => 'Se han insertado los usuarios correctamente.'], 200);
    }


    public function createCSV2(Request $request){

        $validator = Validator::make($request->all(), [
            'csv' => 'required|mimes:csv',
            'course_id' => 'required|exists:courses,id'
        ], [
            'csv.required' => 'Debe enviar un archivo CSV.',
            'csv.mimes' => 'El archivo debe ser de tipo CSV.',
            'course_id.required' => 'El campo ID de curso es obligatorio.',
            'course_id.exists' => 'El ID de curso no existe en la base de datos.'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 409);
        }
        // if (!$request->hasFile('csv')) {
        //     return response()->json(['message' => 'No se ha enviado ningún archivo.'], 400);
        // }
    
        // // Validamos que el archivo sea un csv
        // $file = $request->file('csv');
        // if ($file->getClientOriginalExtension() != 'csv') {
        //     return response()->json(['message' => 'El archivo no es un CSV.'], 400);
        // }
        $file = $request->file('csv');
    
        // Obtenemos la ruta del archivo csv
        $csvPath = $file->getRealPath();
    
        // Validamos que se haya enviado el id del curso
        // $courseId = $request->course_id;
        // if (!$courseId) {
        //     return response()->json(['message' => 'No se ha enviado el id del curso.'], 400);
        // }
   
        $courseId = $request->course_id;

    
        // Abrimos el archivo csv
        $csv = fopen($csvPath, 'r');
    
        // Obtenemos el encabezado del archivo csv
        $header = fgetcsv($csv);
    
        // Inicializamos un array para guardar los errores
        $errors = [];
    
        // Inicializamos un array para guardar los estudiantes creados
        $students = [];

        while ($data = fgetcsv($csv)) {
    
         
            $name = $data[0];
            $surname = $data[1];
            $email = $data[2];

            // Validamos los campos del CSV
            $validator = Validator::make([
                'name' => $name,
                'surname' => $surname,
                'email' => $email,
            ], [
                'name' => 'required|string|max:40',
                'surname' => 'required|string|max:40',
                'email' => 'required|string|max:50|unique:users|regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ], [
                'name.required' => 'El campo nombre es obligatorio.',
                'name.string' => 'El campo nombre debe ser una cadena de caracteres.',
                'name.max' => 'El campo nombre no puede tener más de 40 caracteres.',
                'surname.required' => 'El campo apellido es obligatorio.',
                'surname.string' => 'El campo apellido debe ser una cadena de caracteres.',
                'surname.max' => 'El campo apellido no puede tener más de 40 caracteres.',
                'email.required' => 'El campo correo electrónico es obligatorio.',
                'email.string' => 'El campo correo electrónico debe ser una cadena de caracteres.',
                'email.regex' => 'El campo correo electrónico debe ser una dirección de correo válida.',
                'email.max' => 'El campo correo electrónico no puede tener más de 50 caracteres.',
                'email.unique' => 'Este correo electrónico ya está en uso por otro usuario.'
            ]);

            if ($validator->fails()) {
                // Si falla la validación, agregamos el error al arreglo de errores
                $errors[] = [
                    'message' => "$email ".$validator->errors()->first('email')
                ];
                continue;
            }

            // $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
            // $hashedPassword = Hash::make($password);


            $password = 123456;
            $hashedPassword = Hash::make($password);

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
            $user->email = $email;
            $user->role_id = 2;
            $user->course_id = $courseId;
            $user->password = $hashedPassword;
            $user->save();
        }

        fclose($csv);

        if (!empty($errors)) {
            return response()->json(['message' => 'Error al ingresar los siguientes usuarios', 'errors' => $errors], 400);
        }

        $students = User::where('course_id', $courseId)->get();
        $studentsArray = [];

        foreach ($students as $student) {
            $studentsArray[] = [
                'name' => $student->name,
                'surname' => $student->surname,
                'email' => $student->email,
            ];
        }
        return response()->json(['success' => 'Se han insertado los usuarios correctamente.', 'students' => $studentsArray],200);
    
    }
}
