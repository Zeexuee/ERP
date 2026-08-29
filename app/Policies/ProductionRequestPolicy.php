<?php

namespace App\Policies;

use App\Models\ProductionRequest;
use App\Models\User;

class ProductionRequestPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ProductionRequest $productionRequest): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        // Sales role cannot manually create production requests directly (only via SO trigger)
        return false;
    }

    public function update(?User $user, ProductionRequest $productionRequest): bool
    {
        // Sales role cannot edit production state
        return false;
    }

    public function delete(?User $user, ProductionRequest $productionRequest): bool
    {
        return false;
    }
}
