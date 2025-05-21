<?php

namespace App\Services;

use App\Constants\PaginateConstant;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService
{
  public function getListUser(Request $request)
  {
    $perPage = $request->input('perPage', PaginateConstant::DEFAULT_PER_PAGE->value);
    $page = $request->input('page', PaginateConstant::DEFAULT_PAGE->value);
    $offset = ($page - 1) * $perPage;
    if ($offset < 0) {
      $offset = PaginateConstant::DEFAULT_OFFSET->value;
    }
    $search = $request->input('search', null);
    $query = User::query()->with('role');
    $query = $query->search($search);
    $total = $query->count();
    $users = $query->offset($offset)->limit($perPage)->get();

    $users = $users->map(function ($user) {
      return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at
      ];
    });

    return [
      'data' => $users,
      'pagination' => [
        'total' => $total,
        'page' => (int) $page,
        'perPage' => (int) $perPage
      ]

    ];
  }

  public function getUserById($id)
  {
    $user = User::find($id);
    if (!$user) {
      throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'User']));
    }
    return $user;
  }

  public function createUser($request)
  {
    $role = Role::where('role', $request['role'])->first();
    if (!$role) {
      throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'Role']));
    }
    $user = User::create([
      'name' => $request['name'],
      'email' => $request['email'],
      'role' => $role,
      'password' => Hash::make($request['password']),
    ]);
    return $user;
  }

  public function updateUser($id, $data)
  {
    $user = $this->getUserById($id);
    $user->update($data);
    return new UserResource($user);
  }

  public function deleteUser($id)
  {
    $user = $this->getUserById($id);
    $user->delete();
    return null;
  }
}
