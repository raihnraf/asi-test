<?php

namespace App\Policies;

use App\Models\SalesOrder;
use App\Models\User;

class SalesOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function export(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function update(User $user, SalesOrder $salesOrder): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, SalesOrder $salesOrder): bool
    {
        return $user->isAdmin();
    }
}
