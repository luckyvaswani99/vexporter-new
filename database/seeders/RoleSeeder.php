<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_SUPPORT = 'support';

    public const ROLE_VENDOR_OWNER = 'vendor_owner';

    public const ROLE_VENDOR_STAFF = 'vendor_staff';

    public const ROLE_BUYER = 'buyer';

    /** Platform-wide permissions. Vendor data isolation is handled by policies. */
    public const PERMISSIONS = [
        'vendors.view',
        'vendors.approve',
        'vendors.manage',
        'products.view',
        'products.approve',
        'products.manage',
        'orders.view',
        'orders.manage',
        'rfqs.view',
        'rfqs.manage',
        'payouts.view',
        'payouts.process',
        'reviews.moderate',
        'content.manage',
        'users.manage',
        'settings.manage',
    ];

    private const ROLE_PERMISSIONS = [
        self::ROLE_SUPPORT => [
            'vendors.view', 'products.view', 'orders.view', 'rfqs.view', 'reviews.moderate',
        ],
        self::ROLE_VENDOR_OWNER => [
            'products.manage', 'orders.view', 'orders.manage', 'rfqs.view', 'rfqs.manage', 'payouts.view',
        ],
        self::ROLE_VENDOR_STAFF => [
            'products.manage', 'orders.view', 'rfqs.view',
        ],
        self::ROLE_BUYER => [],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Admin gets everything, including permissions added later.
        Role::findOrCreate(self::ROLE_ADMIN, 'web')->syncPermissions(Permission::all());

        foreach (self::ROLE_PERMISSIONS as $role => $permissions) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissions);
        }
    }
}
