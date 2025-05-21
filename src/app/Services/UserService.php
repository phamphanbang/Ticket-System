<?php

namespace App\Services;

use App\Constants\PaginateConstant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService
{
  public function getListUser(Request $request)
  {
    $limit = $request->input('limit', PaginateConstant::DEFAULT_PER_PAGE);
    $offset = $request->input('offset', PaginateConstant::DEFAULT_OFFSET);
    $search = $request->input('search', null);
    
    $query = User::query();

    $query = $query->search($search);

    $total = $query->count();

    $users = $query->offset($offset)->limit($limit)->get();
    return [
      'data' => $users,
      'total'=> $total,
      'offset' => (int) $offset,
      'limit' => (int) $limit
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

  public function deleteUser($id)
  {
    $user = $this->getUserById($id);
    $user->delete();
    return null;
  }
}
