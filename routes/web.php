<?php

use App\Http\Livewire\ConvertFiletoText;
use App\Http\Livewire\PdfToTextComponent;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/pdf-text' ,  PdfToTextComponent::class)->name('pdf-text');
Route::get('/convert-pdf' ,  ConvertFiletoText::class)->name('convert-pdf');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
