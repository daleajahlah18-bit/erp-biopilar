<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BasePolicy
{
    use HandlesAuthorization;

    protected $module;

    public function viewAny(User $user)
    {
        return $user->can($this->module . '.visible') || $user->can($this->module . '.view');
    }

    public function view(User $user, $model = null)
    {
        return $user->can($this->module . '.view') || $user->can($this->module . '.visible');
    }

    public function create(User $user)
    {
        return $user->can($this->module . '.create');
    }

    public function update(User $user, $model = null)
    {
        return $user->can($this->module . '.edit');
    }

    public function delete(User $user, $model = null)
    {
        return $user->can($this->module . '.delete');
    }

    public function export(User $user, $model = null)
    {
        return $user->can($this->module . '.export');
    }

    public function print(User $user, $model = null)
    {
        return $user->can($this->module . '.print');
    }
    
    public function approve(User $user, $model = null)
    {
        return $user->can($this->module . '.approve');
    }

    public function import(User $user, $model = null)
    {
        return $user->can($this->module . '.import');
    }
}
