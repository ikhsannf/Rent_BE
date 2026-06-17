<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Listing $listing): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isLender();
    }

    public function update(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id && $user->isLender();
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id && $user->isLender();
    }
}
