<?php


use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ArchivedEmployeeController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

Route::get('/inventory/tag/{inventory}', [InventoryController::class, 'tag'])->name('inventory.tag');

Route::middleware(['auth', 'verified'])->group(function () {
// Profile route for all authenticated users (now using controller)
Route::get('/profile', [ProfileController::class, 'show'])->name('profile'); // Updated to use controller

    // Admin-only routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/employee', [EmployeeController::class, 'index'])->name('employee.index');
        Route::get('/employee/create', [EmployeeController::class, 'create'])->name('employee.create');
        Route::post('/employee', [EmployeeController::class, 'store'])->name('employee.store');
        Route::post('employee', [EmployeeController::class, 'store'])->name('employee.store');
    
        Route::get('/employee/archived', [EmployeeController::class, 'archived'])->name('employee.archived');
        
        Route::post('employee/{id}/unarchive', [EmployeeController::class, 'unarchive'])->name('employee.unarchive');
        Route::get('/employee/{employee}/edit', [EmployeeController::class, 'edit'])->name('employee.edit');
        Route::put('/employee/{employee}', [EmployeeController::class, 'update'])->name('employee.update');
        Route::delete('/employee/{employee}', [EmployeeController::class, 'destroy'])->name('employee.destroy');
        Route::post('/org-units', [EmployeeController::class, 'storeOrgUnit'])->name('employee.storeOrgUnit');
        Route::post('/employee/position', [EmployeeController::class, 'position'])->name('employee.position');
        Route::post('employee/salary-grade', [EmployeeController::class, 'storeSalaryGrade'])->name('employee.salaryGrade');
        Route::post('employee/assignment-place', [EmployeeController::class, 'storeAssignmentPlace'])->name('employee.assignmentPlace');
        Route::get('/employee/{employee}/view', [EmployeeController::class, 'view'])->name('employee.view');
  Route::post('/employee/import-excel', [EmployeeController::class, 'importExcel'])->name('employee.import.excel');

        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::post('/users', [UsersController::class, 'store'])->name('users.store');
        Route::get('/users/{users}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
        Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
        Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
        Route::post('archived', [UsersController::class, 'archived'])->name('users.archived');
Route::post('/users/{user}/unarchive', [UsersController::class, 'unarchive'])->name('users.unarchive');
Route::resource('users', UsersController::class);

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
Route::get('/inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::get('/inventory/{inventory}', [InventoryController::class, 'view'])->name('inventory.view');
        
        Route::get('archived', [InventoryController::class, 'archived'])->name('inventory.archived');
        Route::post('/inventory/{inventory}/unarchive', [InventoryController::class, 'unarchive'])->name('inventory.unarchive');
        Route::post('/inventory/tags/print', [InventoryController::class, 'printTags'])->name('inventory.printTags');
        
        
      
   

        Route::get('/report', [ReportController::class, 'index'])->name('report.index')
    ->middleware(['auth', 'verified', 'admin']);
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';