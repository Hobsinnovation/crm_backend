<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('priority')->get();

        return response()->json([
            'success' => true,
            'data'    => $roles,
        ]);
    }
}