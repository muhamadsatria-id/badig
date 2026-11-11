<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserAuthorizationTest extends TestCase
{
    public function test_each_unit_role_only_accesses_its_matching_unit(): void
    {
        foreach ([1, 2, 3, 4] as $roleId) {
            $user = new User(['role_id' => $roleId, 'unit_id' => $roleId]);

            $this->assertTrue($user->hasValidRoleUnitAssignment());
            $this->assertTrue($user->canAccessUnitId($roleId));

            foreach ([1, 2, 3, 4] as $otherUnitId) {
                if ($otherUnitId !== $roleId) {
                    $this->assertFalse($user->canAccessUnitId($otherUnitId));
                }
            }
        }
    }

    public function test_super_admin_can_access_all_units_but_must_not_have_unit_assignment(): void
    {
        $user = new User(['role_id' => User::ROLE_SUPER_ADMIN, 'unit_id' => null]);

        $this->assertTrue($user->hasValidRoleUnitAssignment());
        foreach ([1, 2, 3, 4] as $unitId) {
            $this->assertTrue($user->canAccessUnitId($unitId));
        }

        $invalid = new User(['role_id' => User::ROLE_SUPER_ADMIN, 'unit_id' => 1]);
        $this->assertFalse($invalid->hasValidRoleUnitAssignment());
        $this->assertFalse($invalid->canAccessUnitId(1));
    }

    public function test_mismatched_role_and_unit_is_rejected(): void
    {
        $user = new User(['role_id' => 1, 'unit_id' => 2]);

        $this->assertFalse($user->hasValidRoleUnitAssignment());
        $this->assertFalse($user->canAccessUnitId(1));
        $this->assertFalse($user->canAccessUnitId(2));
    }
}
