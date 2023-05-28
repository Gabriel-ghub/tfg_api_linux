<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AnomalyController;
use App\Http\Controllers\MaterialController;
use App\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(AuthController::class)->group(function () {
    Route::get('me', 'me');
    Route::get('getAll', 'getAll')->middleware('role');
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::post('user/createCSV', 'createCSV2')->middleware('role');
    Route::post('user/createStudent', 'createStudent')->middleware('role');
    Route::post('user/createTeacher', 'createTeacher')->middleware('role');
    Route::post('verify_token', 'verifyToken');
    Route::get('teachers', 'getAllTeachers')->middleware('role');
    Route::get('user/{id}', 'getUserById')->middleware('role');
    Route::put('user', 'update')->middleware('role');
    Route::delete('user/{id}', 'deleteUser')->middleware('role');
    Route::put('user/changePassword', 'changePassword')->middleware('role');
});


Route::controller(CarController::class)->group(function () {
    Route::post('car/create', 'store')->middleware('role');
    Route::put('car/{car_id}', 'updateAndPlate')->middleware('role');
    Route::put('car/{car_id}/update', 'update')->middleware('role');
    Route::delete('car/{car_id}', 'delete')->middleware('role');
    Route::get('car/search', 'search')->middleware('role');
    Route::get('car/{id}', 'show')->middleware('role');
    Route::get('cars', 'index')->middleware('role');
});

Route::controller(CourseController::class)->group(function () {
    Route::get('courses', 'index')->middleware('role');
    Route::post('courses', 'create')->middleware('role');
    Route::delete('courses', 'delete')->middleware('role');
    Route::get('course/{id}', 'show')->middleware('role');
    Route::put('course/{id}/update', 'updateCourse')->middleware('role');
    Route::get('course/{id}/students', 'getStudents')->middleware('role');
});

Route::controller(OrderController::class)->group(function () {
    Route::post('order/create', 'create')->middleware('role');
    Route::delete('order/{order_id}', 'delete')->middleware('role');
    Route::get('order/list', 'showAll')->middleware('role');
    Route::get('order/{id}/details', 'getOrder')->middleware('role');
    Route::post('order/{id}/update', 'update')->middleware('role');
    Route::get('order/{plate}', 'getOrdersByPlate')->middleware('role');
    Route::put('order/close/{order_id}', 'closeOrder')->middleware('role');
    Route::get('order/{order_id}/students', 'getStudents')->middleware('role');
    Route::post('order/attach', 'associate')->middleware('role');
    Route::patch('order/dettach', 'disassociate')->middleware('role');
    Route::get('order/{order_id}/course/{course_id}', 'getUsersFromCourseAndOrder')->middleware('role');
    Route::get('orders/student', 'getOrdersFormStudent');
    Route::put('orders/update/materialsandwork', 'updateMaterialsAndWork');
    Route::get('orders/worksandmaterials/{order_id}', 'getWorksAndMaterials');
    Route::get('order/finalDetails/{order_id}', 'getDataToPDF');
});

Route::controller(WorkController::class)->group(function () {
    Route::post('work/create', 'create')->middleware('role');
    Route::post('work/attach', 'associate')->middleware('role');
    Route::patch('work/dettach', 'disassociate')->middleware('role');
    Route::post('work/users', 'getUsersByWorkId')->middleware('role');
    Route::get('works/{order_id}', 'getWorksByOrderId')->middleware('role');
    Route::put('work/update', 'update')->middleware('role');
    Route::delete('work/delete/{id}', 'deleteWork');
    Route::get('work/{id}/students', 'getStudents')->middleware('role');
    Route::get('work/{work_id}/course/{course_id}', 'getUsersFromCourseAndWork')->middleware('role');
    Route::get('works/student', 'getWorksByStudent');
    Route::patch('works/change_state', 'changeState2');
    Route::post('work/student/create', 'createByStudent');
    // Route::get('works/{id}','getWorkDetails');
});

Route::controller(AnomalyController::class)->group(function () {
    Route::post('anomaly/create', 'create')->middleware('role');
    Route::post('anomaly/createOne', 'createOne')->middleware('role');
    Route::post('anomaly/update', 'update')->middleware('role');
    Route::delete('anomaly/destroy', 'destroy')->middleware('role');
});


Route::controller(MaterialController::class)->group(function () {
    // Route::post('work/create', 'create')->middleware('role');
    // Route::post('work/attach', 'associate')->middleware('role');
    // Route::patch('work/dettach', 'disassociate')->middleware('role');
    // Route::post('work/users', 'getUsersByWorkId')->middleware('role');
    // Route::get('works/{order_id}', 'getWorksByOrderId')->middleware('role');
    // Route::put('work/update', 'update')->middleware('role');
    Route::delete('material/delete/{id}', 'deleteMaterial');
    Route::post('material', 'createMaterial');
    Route::put('material', 'updateMaterialPrice')->middleware('role');
    // Route::get('work/{id}/students', 'getStudents')->middleware('role');
    // Route::get('work/{work_id}/course/{course_id}', 'getUsersFromCourseAndWork')->middleware('role');
    // Route::get('works/student', 'getWorksByStudent');
    // Route::patch('works/change_state', 'changeState2');
    // Route::get('works/{id}','getWorkDetails');
});
