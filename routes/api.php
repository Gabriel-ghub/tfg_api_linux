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
    Route::get('getAllTeachers', 'getAllTeachers');
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::post('user/createCSV', 'createStudentsFromCSV');
    Route::post('user/createStudent', 'createStudent');
    Route::post('verify_token', 'verifyToken');
});


Route::controller(CarController::class)->group(function () {
    Route::post('car/create', 'store');
    Route::post('car/modify', 'modify');
    Route::post('car/delete', 'delete');
    Route::get('car/search', 'search');
});

Route::controller(CourseController::class)->group(function () {
    Route::get('courses/index', 'index');
    Route::post('courses/create', 'create');
    Route::delete('courses/delete', 'delete');
    Route::get('course/{id}', 'show');
    Route::put('course/{id}/update', 'updateCourse');
    Route::get('course/{id}/students', 'getStudents');
});

Route::controller(OrderController::class)->group(function () {
    Route::post('order/create', 'create');
    Route::post('order/delete', 'delete');
    Route::get('order/list', 'showAll');
    Route::get('order/{id}/details', 'getOrder');
    Route::post('order/{id}/update', 'update');
    Route::get('order/{plate}', 'getOrdersByPlate');
});

Route::controller(WorkController::class)->group(function () {
    Route::post('work/create', 'create');
    Route::post('work/attach', 'associate');
    Route::patch('work/dettach', 'disassociate');
    Route::post('work/users', 'getUsersByWorkId');
    Route::get('work/{order_id}', 'getWorksByOrderId');
    Route::put('work/update', 'update');
    Route::delete('work/delete/{id}', 'delete');
    Route::get('work/{id}/students', 'getStudents');
    Route::get('work/{work_id}/course/{course_id}', 'getUsersFromCourseAndWork');
    Route::get('works/student','getWorksByStudent');
    Route::patch('works/change_state','changeState');
});

Route::controller(AnomalyController::class)->group(function () {
    Route::post('anomaly/create', 'create');
    Route::post('anomaly/update', 'update');
    Route::post('anomaly/destroy', 'destroy');
});
