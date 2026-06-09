<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Super\{RoleController, PermissionController, UserManageController, UserController};
use App\Http\Controllers\Admin\{
    PortfolioController,
    SettingController,
    ServiceController,
    TechStackController,
    PageController,
    LeadController,
    PaketController,
    ServerController,
    PelangganController,
    BulanController,
    TagihanController,
    PaymentController
};

Route::middleware(['auth'])
    ->prefix('super')->name('super.')->group(function () {

        // Dashboard
        Route::view('/', 'super.dashboard')->name('dashboard')
            ->middleware('permission:dashboard.view');

        /* ===== Access Control ===== */

        // ROLES
        Route::middleware('permission:role.read')->get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::middleware('permission:role.read')->get('/roles/dt', [RoleController::class, 'datatable'])->name('roles.dt');
        Route::middleware('permission:role.create')->get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::middleware('permission:role.create')->post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::middleware('permission:role.update')->get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::middleware('permission:role.update')->put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::middleware('permission:role.delete')->delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::middleware('permission:role.update')->get('/roles/{role}/permissions', [RoleController::class, 'editPermissions'])->name('roles.permissions.edit');
        Route::middleware('permission:role.update')->put('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

        // PERMISSIONS
        Route::middleware('permission:permission.read')->get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::middleware('permission:permission.read')->get('/permissions/dt', [PermissionController::class, 'datatable'])->name('permissions.dt');
        Route::middleware('permission:permission.create')->get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::middleware('permission:permission.create')->post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::middleware('permission:permission.update')->get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::middleware('permission:permission.update')->put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::middleware('permission:permission.delete')->delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

        // USERS (manajemen user)
        Route::prefix('users')->name('user.')->group(function () {
            Route::middleware('permission:user.read')->get('/', [UserController::class, 'index'])->name('index');
            Route::middleware('permission:user.read')->get('/dt', [UserController::class, 'datatable'])->name('dt');
            Route::middleware('permission:user.create')->get('/create', [UserController::class, 'create'])->name('create');
            Route::middleware('permission:user.create')->post('/', [UserController::class, 'store'])->name('store');
            Route::middleware('permission:user.update')->get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::middleware('permission:user.update')->put('/{user}', [UserController::class, 'update'])->name('update');
            Route::middleware('permission:user.delete')->delete('/{user}', [UserController::class, 'destroy'])->name('destroy');

            Route::middleware('permission:user.update')->put('/{user}/roles', [UserManageController::class, 'syncRoles'])->name('roles.sync');
            Route::middleware('permission:user.update')->put('/{user}/perms', [UserManageController::class, 'syncPermissions'])->name('perms.sync');
        });

        /* ===== Settings ===== */

        // PAKETS
        Route::middleware('permission:pakets.view')->get('pakets', [PaketController::class, 'index'])->name('pakets.index');
        Route::middleware('permission:pakets.view')->get('pakets/dt', [PaketController::class, 'datatable'])->name('pakets.dt');
        Route::middleware('permission:pakets.create')->post('pakets', [PaketController::class, 'store'])->name('pakets.store');
        Route::middleware('permission:pakets.update')->put('pakets/{paket}', [PaketController::class, 'update'])->name('pakets.update');
        Route::middleware('permission:pakets.delete')->delete('pakets/{paket}', [PaketController::class, 'destroy'])->name('pakets.destroy');
        Route::middleware('permission:pakets.view')->get('pakets/export/xlsx', [PaketController::class, 'export'])->name('pakets.export');

        // SERVERS
        Route::middleware('permission:servers.view')->get('servers', [ServerController::class, 'index'])->name('servers.index');
        Route::middleware('permission:servers.view')->get('servers/dt', [ServerController::class, 'dt'])->name('servers.dt');
        Route::middleware('permission:servers.view')->get('servers/export/xlsx', [ServerController::class, 'export'])->name('servers.export');
        Route::middleware('permission:servers.create')->post('servers', [ServerController::class, 'store'])->name('servers.store');
        Route::middleware('permission:servers.update')->put('servers/{server}', [ServerController::class, 'update'])->name('servers.update');
        Route::middleware('permission:servers.delete')->delete('servers/{server}', [ServerController::class, 'destroy'])->name('servers.destroy');

        // PELANGGANS
        Route::middleware('permission:pelanggans.view')->get('pelanggans', [PelangganController::class, 'index'])->name('pelanggans.index');
        Route::middleware('permission:pelanggans.view')->get('pelanggans/dt', [PelangganController::class, 'dt'])->name('pelanggans.dt');
        Route::middleware('permission:pelanggans.view')->get('pelanggans/export/xlsx', [PelangganController::class, 'export'])->name('pelanggans.export');
        Route::middleware('permission:pelanggans.create')->post('pelanggans', [PelangganController::class, 'store'])->name('pelanggans.store');
        Route::middleware('permission:pelanggans.update')->put('pelanggans/{pelanggan}', [PelangganController::class, 'update'])->name('pelanggans.update');
        Route::middleware('permission:pelanggans.delete')->delete('pelanggans/{pelanggan}', [PelangganController::class, 'destroy'])->name('pelanggans.destroy');

        // BULANS
        Route::middleware('permission:bulans.view')->get('bulans', [BulanController::class, 'index'])->name('bulans.index');
        Route::middleware('permission:bulans.view')->get('bulans/dt', [BulanController::class, 'dt'])->name('bulans.dt');
        Route::middleware('permission:bulans.view')->get('bulans/export/xlsx', [BulanController::class, 'export'])->name('bulans.export');
        Route::middleware('permission:bulans.create')->post('bulans', [BulanController::class, 'store'])->name('bulans.store');
        Route::middleware('permission:bulans.update')->put('bulans/{bulan}', [BulanController::class, 'update'])->name('bulans.update');
        Route::middleware('permission:bulans.delete')->delete('bulans/{bulan}', [BulanController::class, 'destroy'])->name('bulans.destroy');

        /* ===== Payment ===== */

        // TAGIHANS
        Route::middleware('permission:tagihans.view')->get('tagihans', [TagihanController::class, 'index'])->name('tagihans.index');
        Route::middleware('permission:tagihans.view')->get('tagihans/dt', [TagihanController::class, 'dt'])->name('tagihans.dt');
        Route::middleware('permission:tagihans.view')->get('tagihans/export/xlsx', [TagihanController::class, 'export'])->name('tagihans.export');

        Route::middleware('permission:tagihans.create')->post('tagihans', [TagihanController::class, 'store'])->name('tagihans.store');
        Route::middleware('permission:tagihans.update')->put('tagihans/{tagihan}', [TagihanController::class, 'update'])->name('tagihans.update');
        Route::middleware('permission:tagihans.delete')->delete('tagihans/{tagihan}', [TagihanController::class, 'destroy'])->name('tagihans.destroy');

        // generate batch
        Route::middleware('permission:tagihans.create')->post('tagihans/generate', [TagihanController::class, 'generate'])->name('tagihans.generate');

        // unpaid views
        Route::middleware('permission:tagihans.view')->get('tagihans/unpaid', [TagihanController::class, 'unpaid'])->name('tagihans.unpaid');
        Route::middleware('permission:tagihans.view')->get('tagihans/unpaid/dt', [TagihanController::class, 'unpaidDt'])->name('tagihans.unpaid.dt');
        Route::middleware('permission:tagihans.view')->get('tagihans/unpaid/export/xlsx', [TagihanController::class, 'unpaidExport'])->name('tagihans.unpaid.export');

        // PAYMENTS
        Route::middleware('permission:payments.view')->get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::middleware('permission:payments.view')->get('payments/dt', [PaymentController::class, 'dt'])->name('payments.dt');
        Route::middleware('permission:payments.view')->get('payments/summary', [PaymentController::class, 'summary'])->name('payments.summary');
        Route::middleware('permission:payments.view')->get('payments/export/xlsx', [PaymentController::class, 'export'])->name('payments.export');

        Route::middleware('permission:payments.create')->post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::middleware('permission:payments.delete')->delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        Route::middleware('permission:payments.view')->get('payments/lookup', [PaymentController::class, 'lookup'])->name('payments.lookup');
        Route::middleware('permission:payments.view')->get('payments/find', [PaymentController::class, 'find'])->name('payments.find');



        Route::middleware('permission:payments.view')->post('payments/bulk', [PaymentController::class, 'payMultiple'])->name('payments.bulk');


        Route::get('/monitoring', \App\Http\Controllers\Super\MonitoringPageController::class)
            ->name('monitoring.index');

        // Endpoint JSON (dari jawaban sebelumnya)
        Route::get('/monitoring/{serverId}', [\App\Http\Controllers\Super\MonitoringController::class, 'index'])
            ->name('monitoring.json');
    });
