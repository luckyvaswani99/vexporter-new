<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('products.manage') || $user->can('products.view');
    }

    /** Buyers only ever see approved products from approved vendors. */
    public function view(?User $user, Product $product): bool
    {
        if ($product->is_active && $product->approval_status === Product::APPROVAL_APPROVED && $product->vendor?->isApproved()) {
            return true;
        }

        return $user !== null && $this->ownsProduct($user, $product);
    }

    public function create(User $user): bool
    {
        return $user->can('products.manage') && $user->vendor()->where('status', 'approved')->exists();
    }

    public function update(User $user, Product $product): bool
    {
        return $this->ownsProduct($user, $product) && $user->can('products.manage');
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }

    public function approve(User $user, Product $product): bool
    {
        return $user->can('products.approve');
    }

    private function ownsProduct(User $user, Product $product): bool
    {
        $vendor = $product->vendor;

        return $vendor !== null
            && ($vendor->user_id === $user->id || $vendor->staff()->whereKey($user->id)->exists());
    }
}
