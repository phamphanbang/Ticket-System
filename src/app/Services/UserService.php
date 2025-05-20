<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class UserService
{
  public function getListUser()
  {
    $query = User::query();


    $users = $query->get();
    return [
      'users' => $users,
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
    $role = Role::where('role',$request['role'])->first();
    if (!$role) {
      throw new ModelNotFoundException(__('messages.model_not_found', ['model' => 'Role']));
    }
    $user = User::create([
      'name' => $request['name'],
      'email' => $request['email'],
      'role_id' => $role->id,
      'password' => Hash::make($request['password']),
    ]);
    return $user;
  }

  public function updateUser($id, $data)
  {
    $user = $this->getUserById($id);
    $user->update($data);
    return $user;
  }

  public function deleteUser($id) {
    $user = $this->getUserById($id);
    $user->delete();
    return null;
  }
}
