<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    private $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function index(Request $request)
    {
        $data = $this->userService->getListUser($request);
        return $this->success(
            $data,
            __('messages.model_list', ['model' => 'User'])
        );
    }

    public function show($id)
    {
        $data = $this->userService->getUserById(($id));
        return $this->success(
            $data,
            __('messages.model_get_success', ['model' => 'User'])
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,user',
        ]);

        $data = $this->userService->createUser($validated);

        return $this->success($data, __('messages.model_created', ['model' => 'User']));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $validated = $request->validated();
        if(array_key_exists('password',$validated) && $validated['password'] == null){
            unset($validated['password']);
        }
        $data = $this->userService->updateUser($id,$validated);

        return $this->success(
            $data,
            __('messages.model_updated', ['model' => 'User'])
        );
    }

    public function destroy($id)
    {
        $data = $this->userService->deleteUser($id);
        return $this->success(
            $data,
            __('messages.model_deleted', ['model' => 'User'])
        );
    }

    public function clientIndex($request) {
        $data = $this->userService->getListClient($request);
        return $this->success(
            $data,
            __('messages.model_list', ['model' => 'User'])
        );
    }
}
