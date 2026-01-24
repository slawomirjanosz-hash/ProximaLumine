<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrmCustomerTypeController;

Route::middleware(['web', 'auth', 'can:can_settings'])->group(function () {
    Route::get('/crm/customer-types', [CrmCustomerTypeController::class, 'index'])->name('crm.customer-types');
    Route::post('/crm/customer-types', [CrmCustomerTypeController::class, 'store'])->name('crm.customer-types.store');
    Route::put('/crm/customer-types/{id}', [CrmCustomerTypeController::class, 'update'])->name('crm.customer-types.update');
    Route::delete('/crm/customer-types/{id}', [CrmCustomerTypeController::class, 'destroy'])->name('crm.customer-types.destroy');
});
