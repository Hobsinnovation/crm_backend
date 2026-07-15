<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ActivityLogController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (token required)
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Notifications (apni notifications — permission ki zaroorat nahi)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    // Roles list
    Route::get('/roles', [RoleController::class, 'index']);

    // Users management
    Route::middleware('permission:users.view')->get('/users', [UserController::class, 'index']);
    Route::middleware('permission:users.view')->get('/users/{user}', [UserController::class, 'show']);
    Route::middleware('permission:users.create')->post('/users', [UserController::class, 'store']);
    Route::middleware('permission:users.update')->put('/users/{user}', [UserController::class, 'update']);
    Route::middleware('permission:users.update')->patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::middleware('permission:users.delete')->delete('/users/{user}', [UserController::class, 'destroy']);

    // Clients management
    Route::middleware('permission:clients.view')->get('/clients', [ClientController::class, 'index']);
    Route::middleware('permission:clients.view')->get('/clients/{client}', [ClientController::class, 'show']);
    Route::middleware('permission:clients.create')->post('/clients', [ClientController::class, 'store']);
    Route::middleware('permission:clients.update')->put('/clients/{client}', [ClientController::class, 'update']);
    Route::middleware('permission:clients.delete')->delete('/clients/{client}', [ClientController::class, 'destroy']);
     

    
    // Leads management
    Route::middleware('permission:leads.view')->get('/leads', [LeadController::class, 'index']);
    Route::middleware('permission:leads.view')->get('/leads/assignable-users', [LeadController::class, 'assignableUsers']);
    Route::middleware('permission:leads.view')->get('/leads/{lead}', [LeadController::class, 'show']);
    Route::middleware('permission:leads.create')->post('/leads', [LeadController::class, 'store']);
    Route::middleware('permission:leads.update')->put('/leads/{lead}', [LeadController::class, 'update']);
    Route::middleware('permission:leads.assign')->patch('/leads/{lead}/assign', [LeadController::class, 'assign']);
    Route::middleware('permission:clients.create')->post('/leads/{lead}/convert', [LeadController::class, 'convert']);
    Route::middleware('permission:leads.delete')->delete('/leads/{lead}', [LeadController::class, 'destroy']);
    
    // Domains management
    Route::middleware('permission:domains.view')->get('/domains', [DomainController::class, 'index']);
    Route::middleware('permission:domains.view')->get('/domains/clients-list', [DomainController::class, 'clientsList']);
    Route::middleware('permission:domains.view')->get('/domains/{domain}', [DomainController::class, 'show']);
    Route::middleware('permission:domains.create')->post('/domains', [DomainController::class, 'store']);
    Route::middleware('permission:domains.update')->put('/domains/{domain}', [DomainController::class, 'update']);
    Route::middleware('permission:domains.update')->patch('/domains/{domain}/toggle-renewal', [DomainController::class, 'toggleAutoRenewal']);
    Route::middleware('permission:domains.delete')->delete('/domains/{domain}', [DomainController::class, 'destroy']);

    // Invoices management
    Route::middleware('permission:invoices.view')->get('/invoices', [InvoiceController::class, 'index']);
    Route::middleware('permission:invoices.view')->get('/invoices/by-client', [InvoiceController::class, 'byClient']);
    Route::middleware('permission:invoices.view')->get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::middleware('permission:invoices.create')->post('/invoices', [InvoiceController::class, 'store']);
    Route::middleware('permission:invoices.update')->put('/invoices/{invoice}', [InvoiceController::class, 'update']);
    Route::middleware('permission:invoices.update')->patch('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid']);
    Route::middleware('permission:invoices.delete')->delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);

     // Dashboard stats
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    
    // Activity logs (audit trail — sirf users.view walon ke liye i.e. admins)
    Route::middleware('permission:users.view')->get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::middleware('permission:users.view')->get('/activity-logs/filters', [ActivityLogController::class, 'filters']);


    });