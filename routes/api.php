<?php

use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('/user')->group(function () {
    //Rota para criar usuario
    Route::post('/register', [UserController::class, 'register']);

    //Rota de login
    Route::post('/login', [UserController::class, 'login']);
});

Route::middleware(['jwt.verify'])->group(function () {
    //Rota de logout
    Route::post('/user/logout', [UserController::class, 'signout']);

    Route::prefix('/surveys')->group(function () {
        //Rota para pegar todas as enquetes ativas
        Route::get('/', [SurveyController::class, 'index']);
    
        //Rota para consultar enquete por nome e id
        Route::get('/{name}', [SurveyController::class, 'getByName'])->whereAlpha('name');
        Route::get('/{id}', [SurveyController::class, 'getById'])->whereNumber('id');
    
        //Rota para cadastrar nova enquete
        Route::post('/create', [SurveyController::class, 'create']);
    
        //Rota para enable/desativar enquete
        Route::post('/{id}/enable', [SurveyController::class, 'enable']);
    
        //Rota para votar
        Route::post('/{id}/vote', [SurveyController::class, 'vote']);
    });
});
