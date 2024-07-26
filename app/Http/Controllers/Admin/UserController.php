<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Requests\UserReqeust;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Repositories\Admin\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserRepository $userRepository) {
        //
    }

    public function index(Request $request): View|JsonResponse
    {
        return $request->ajax()
            ? $this->userRepository->getAsyncListingData($request)
            : view('admin.users.index');
    }

    public function create(): View
    {
        return view('admin.users.alter', [
            'action' => 'Add',
            'actionUrl' => route('admin.users.store'),
        ]);
    }

    public function store(UserReqeust $request): RedirectResponse
    {
        $this->userRepository->create($request->validated());
        return redirect(route('admin.users.index'))->with('success', config('constants.default_data_insert_msg'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.alter', [
            'user' => $user,
            'action' => 'Edit',
            'actionUrl' => route('admin.users.update', $user),
        ]);
    }

    public function update(UserReqeust $request, User $user): RedirectResponse|JsonResponse
    {
        $this->userRepository->update($user->id, $request->validated());
        return $request->ajax()
            ? response()->json(['success' => true, 'message' => config('constants.default_data_update_msg')])
            : redirect(route('admin.users.index'))->with('success', config('constants.default_data_update_msg'));
    }

    public function destroy(User $user, Request $request): RedirectResponse|JsonResponse
    {
        $user->delete();
        return $request->ajax()
            ? response()->json(['success' => true,'message' => config('constants.default_data_deleted_msg')])
            : redirect(route('admin.users.index'))->with('success', config('constants.default_data_deleted_msg'));
    }

    public function handleMassUpdate(Request $request)
    {
        $ids = $request->ids;
        $operationType = $request->operationType;
        $this->userRepository->updateMultiple(User::class, $ids, $operationType);

        return response()->json([
            'success' => true,
            'message' => config('constants.default_data_update_msg')
        ]);
    }
}
