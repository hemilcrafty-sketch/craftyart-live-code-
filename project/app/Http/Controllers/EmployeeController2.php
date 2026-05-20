<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Utils\RoleManager;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class EmployeeController extends AppBaseController
{
    private mixed $roles;
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->roles = config('role');
    }

    public function show(User $user)
    {
        $employeeArray = User::where("user_type", "!=", UserRole::ADMIN->id())
            ->with('leader') // Only load leader relationship
            ->get();

        // Get SEO Executives (user_type = SEO_EXECUTIVE)
        $showExecutive = User::where('user_type', UserRole::SEO_EXECUTIVE->id())->get();

        // Changes get Sales Manager
        $salesManagers = User::where('user_type', UserRole::SALES_MANAGER->id())->get();

        $roles = $this->roles;
        return view('users/show_employee', compact('employeeArray', 'roles', 'showExecutive', 'salesManagers'));
    }

    public function create(Request $request)
    {
        $res = new User();
        $res->name = $request->input('name');
        $res->email = $request->input('email');
        $res->password = Hash::make($request->input('password'));
        $res->user_type = $request->input('user_type');

        // For both SEO Intern and Sales, use team_leader_id column
        // Changes add sales Role
        if (($request->input('user_type') == UserRole::SEO_INTERN->id() ||
                $request->input('user_type') == UserRole::SALES->id()) &&
            $request->input('team_leader_id') !== null) {
            $res->team_leader_id = $request->input('team_leader_id');
        }

        $res->save();

        return response()->json([
            'success' => "Created Successfully"
        ]);
    }

    public function update(Request $request, User $user)
    {
        $res = User::find($request->id);
        $res->name = $request->input('name');
        $res->email = $request->input('email');
        $res->user_type = $request->input('user_type');

        // Changes add sales Role
        if (($request->input('user_type') == UserRole::SEO_INTERN->id() ||
                $request->input('user_type') == UserRole::SALES->id()) &&
            $request->input('team_leader_id') !== null) {
            $res->team_leader_id = $request->input('team_leader_id');
        } else {
            $res->team_leader_id = 0;
        }

        $res->save();

        return response()->json([
            'success' => "Updated Successfully"
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $res = User::find($id);
        $res->password = Hash::make($request->input('new_password'));
        $res->save();

        return response()->json([
            'success' => "Reset Successfully"
        ]);
    }

    public function destroy(Request $request)
    {
        $isAdmin = RoleManager::isAdmin(Auth::user()->user_type);

        $res = User::find($request->id);
        if ($res->status == 1) {
            $res->status = 0;
        } else {
            if (!$isAdmin) {
                return response()->json([
                    'error' => "Ask admin to change."
                ]);
            }
            $res->status = 1;
        }
        $res->save();
        return response()->json([
            'success' => "Delete Successfully"
        ]);
    }
}