<?php

namespace App\Enums;

enum RoleEnum
{
    const SuperAdmin = 'super_admin';
    const INVENTORY_ADMIN = 'inventory_admin';
    const Mechanical = 'mechanical';
    const Driver = 'driver';
    const Customer = 'customer';
}
