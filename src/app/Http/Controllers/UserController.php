<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function index()
    {
        $data = $this->userService->getListUser();
        return response()->success(
            $data['users'],
            __('messages.model_list', ['model' => 'User'])
        );
    }

    // Show a single user
    public function show($id)
    {
        $data = $this->userService->getUserById(($id));
        return response()->success(
            $data,
            __('messages.model_get_success', ['model' => 'User'])
        );
    }

    // Create a new user
    public function store(CreateUserRequest $request)
    {
        $validated = $request->validated();

        $data = $this->userService->createUser($validated);

        return response()->success($data, __('messages.model_created', ['model' => 'User']));
    }

    // Update an existing user
    public function update(UpdateUserRequest $request, $id)
    {
        $validated = $request->validated();

        $data = $this->userService->updateUser($id,$validated);

        return response()->success(
            $data,
            __('messages.model_updated', ['model' => 'User'])
        );
    }

    // Delete a user
    public function destroy($id)
    {
        $data = $this->userService->deleteUser($id);
        return response()->success(
            $data,
            __('messages.model_deleted', ['model' => 'User'])
        );
    }
}
