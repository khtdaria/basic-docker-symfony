<?php

declare(strict_types=1);

namespace App\Enum;

enum UserRole: string
{
    case SuperAdmin = 'ROLE_SUPER_ADMIN';
    case TenantAdmin = 'ROLE_TENANT_ADMIN';
    case TenantUser = 'ROLE_TENANT_USER';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::TenantAdmin => 'Tenant Admin',
            self::TenantUser => 'Tenant User',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::TenantAdmin => 'warning',
            self::TenantUser => 'success',
        };
    }
}
