<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreOperatorRequest;
use App\Http\Requests\Platform\UpdateOperatorRequest;
use App\Models\Platform\PlatformUser;
use App\Services\Platform\PlatformAudit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * CRUD for platform operators (safm_platform.platform_users).
 *
 * The whole resource sits behind 'platform.ability:operators.manage', which only
 * the owner role holds, so no further per-action ability check is needed. The
 * controller additionally protects the current operator from locking themselves
 * out (self-deactivation / self-deletion).
 */
class OperatorController extends Controller
{
    /**
     * List every operator, including soft-deleted-free active/inactive rows.
     */
    public function index(): View
    {
        return view('platform.operators.index', [
            'operators' => PlatformUser::query()
                ->orderBy('name')
                ->paginate(20),
            'roles' => $this->roles(),
        ]);
    }

    /**
     * Show the new-operator form.
     */
    public function create(): View
    {
        return view('platform.operators.create', [
            'roles' => $this->roles(),
        ]);
    }

    /**
     * Persist a new operator. The password is hashed by the model's 'hashed'
     * cast.
     */
    public function store(StoreOperatorRequest $request, PlatformAudit $audit): RedirectResponse
    {
        $data = $request->validated();

        $operator = PlatformUser::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $audit->log('operator.created', $operator, null, [
            'email' => $operator->email,
            'role' => $operator->role,
        ]);

        return redirect()
            ->route('platform.operators.index')
            ->with('success', __('app.created_success'));
    }

    /**
     * Show the edit form for an operator.
     */
    public function edit(PlatformUser $operator): View
    {
        return view('platform.operators.edit', [
            'operator' => $operator,
            'roles' => $this->roles(),
        ]);
    }

    /**
     * Update an operator. A blank password leaves the current one untouched. The
     * acting operator may not deactivate or demote themselves.
     */
    public function update(UpdateOperatorRequest $request, PlatformUser $operator, PlatformAudit $audit): RedirectResponse
    {
        $data = $request->validated();
        $isSelf = $this->isCurrentOperator($operator);

        $attributes = [
            'name' => $data['name'],
            'email' => $data['email'],
            // Preserve own role and active flag to avoid self-lockout.
            'role' => $isSelf ? $operator->role : $data['role'],
            'is_active' => $isSelf ? true : (bool) ($data['is_active'] ?? false),
        ];

        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        $operator->fill($attributes);
        $changed = array_keys($operator->getDirty());
        $operator->save();

        $audit->log('operator.updated', $operator, null, [
            'changed' => $changed,
        ]);

        return redirect()
            ->route('platform.operators.index')
            ->with('success', __('app.updated_success'));
    }

    /**
     * Soft-delete an operator. The acting operator may not delete themselves.
     */
    public function destroy(PlatformUser $operator, PlatformAudit $audit): RedirectResponse
    {
        if ($this->isCurrentOperator($operator)) {
            return redirect()
                ->route('platform.operators.index')
                ->with('error', __('app.error_occurred'));
        }

        $email = $operator->email;
        $operator->delete();

        $audit->log('operator.deleted', null, null, [
            'email' => $email,
        ]);

        return redirect()
            ->route('platform.operators.index')
            ->with('success', __('app.deleted_success'));
    }

    /**
     * Whether $operator is the operator currently signed in.
     */
    private function isCurrentOperator(PlatformUser $operator): bool
    {
        $current = Auth::guard('platform')->user();

        return $current !== null && $current->getKey() === $operator->getKey();
    }

    /**
     * The valid operator roles, for the role selector.
     *
     * @return array<int, string>
     */
    private function roles(): array
    {
        return ['owner', 'admin', 'support', 'billing'];
    }
}
