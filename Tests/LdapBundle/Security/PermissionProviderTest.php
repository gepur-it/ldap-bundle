<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since: 10.11.17
 */

namespace GepurIt\LdapBundle\Tests\Security;

use GepurIt\LdapBundle\Security\PermissionProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class PermissionProviderTest
 * @package LdapBundle\Security
 */
class PermissionProviderTest extends TestCase
{
    /**
     * @param $permissionName
     * @param $expectation
     * @dataProvider nameDataProvider
     */
    public function testGetPermissionMask($permissionName, $expectation)
    {
        $permissionProvider = new PermissionProvider();
        $this->assertEquals($expectation, $permissionProvider->getPermissionMask($permissionName));
    }

    public function nameDataProvider()
    {
        return [
            '"READ" permission'                                         => [
                'permissionName' => 'READ',
                'expectedMask'   => PermissionProvider::MASK__READ,
            ],
            '"WRITE" permission'                                        => [
                'permissionName' => 'WRITE',
                'expectedMask'   => PermissionProvider::MASK__WRITE,
            ],
            '"APPROVE" permission'                                      => [
                'permissionName' => 'APPROVE',
                'expectedMask'   => PermissionProvider::MASK__APPROVE,
            ],
            '"DELETE" permission'                                       => [
                'permissionName' => 'DELETE',
                'expectedMask'   => PermissionProvider::MASK__DELETE,
            ],
            'unknown permission should return "deny" mask (all zeroes)' => [
                'permissionName' => 'asdfojisdagon',
                'expectedMask'   => PermissionProvider::MASK__DENY_ALL,
            ],
        ];
    }

    /**
     * @param $permissionName
     * @param $permission
     * @param $grantedExpectation
     * @dataProvider permissionProvider
     */
    public function testIsGranted($permissionName, $permission, $grantedExpectation)
    {
        $permissionProvider = new PermissionProvider();
        $this->assertEquals($grantedExpectation, $permissionProvider->isGranted($permissionName, $permission));
    }

    /**
     * @return array
     */
    public function permissionProvider()
    {
        return [
            'permission: 1, permissionName: READ'      => [
                'permissionName'     => 'READ',
                'permission'         => 1,
                'grantedExpectation' => true,
            ],
            'permission: 2, permissionName: READ'      => [
                'permissionName'     => 'READ',
                'permission'         => 2,
                'grantedExpectation' => false,
            ],
            'permission: 3, permissionName: READ'      => [
                'permissionName'     => 'READ',
                'permission'         => 3,
                'grantedExpectation' => true,
            ],
            'permission: 4, permissionName: READ'      => [
                'permissionName'     => 'READ',
                'permission'         => 4,
                'grantedExpectation' => false,
            ],
            'permission: 5, permissionName: READ'      => [
                'permissionName'     => 'READ',
                'permission'         => 5,
                'grantedExpectation' => true,
            ],
            'permission: 6, permissionName: READ'      => [
                'permissionName'     => 'READ',
                'permission'         => 6,
                'grantedExpectation' => false,
            ],
            'permission: 7, permissionName: READ'      => [
                'permissionName'     => 'READ',
                'permission'         => 7,
                'grantedExpectation' => true,
            ],
            'permission: 8, permissionName: READ'      => [
                'permissionName'     => 'READ',
                'permission'         => 8,
                'grantedExpectation' => false,
            ],
            'permission: 9, permissionName: READ'      => [
                'permissionName'     => 'READ',
                'permission'         => 9,
                'grantedExpectation' => true,
            ],
            'permission: 10, permissionName: READ'     => [
                'permissionName'     => 'READ',
                'permission'         => 10,
                'grantedExpectation' => false,
            ],
            'permission: 11, permissionName: READ'     => [
                'permissionName'     => 'READ',
                'permission'         => 11,
                'grantedExpectation' => true,
            ],
            'permission: 12, permissionName: READ'     => [
                'permissionName'     => 'READ',
                'permission'         => 12,
                'grantedExpectation' => false,
            ],
            'permission: 13, permissionName: READ'     => [
                'permissionName'     => 'READ',
                'permission'         => 13,
                'grantedExpectation' => true,
            ],
            'permission: 14, permissionName: READ'     => [
                'permissionName'     => 'READ',
                'permission'         => 14,
                'grantedExpectation' => false,
            ],
            'permission: 15, permissionName: READ'     => [
                'permissionName'     => 'READ',
                'permission'         => 15,
                'grantedExpectation' => true,
            ],
            'permission: 1, permissionName: WRITE'     => [
                'permissionName'     => 'WRITE',
                'permission'         => 1,
                'grantedExpectation' => false,
            ],
            'permission: 2, permissionName: WRITE'     => [
                'permissionName'     => 'WRITE',
                'permission'         => 2,
                'grantedExpectation' => true,
            ],
            'permission: 3, permissionName: WRITE'     => [
                'permissionName'     => 'WRITE',
                'permission'         => 3,
                'grantedExpectation' => true,
            ],
            'permission: 4, permissionName: WRITE'     => [
                'permissionName'     => 'WRITE',
                'permission'         => 4,
                'grantedExpectation' => false,
            ],
            'permission: 5, permissionName: WRITE'     => [
                'permissionName'     => 'WRITE',
                'permission'         => 5,
                'grantedExpectation' => false,
            ],
            'permission: 6, permissionName: WRITE'     => [
                'permissionName'     => 'WRITE',
                'permission'         => 6,
                'grantedExpectation' => true,
            ],
            'permission: 7, permissionName: WRITE'     => [
                'permissionName'     => 'WRITE',
                'permission'         => 7,
                'grantedExpectation' => true,
            ],
            'permission: 8, permissionName: WRITE'     => [
                'permissionName'     => 'WRITE',
                'permission'         => 8,
                'grantedExpectation' => false,
            ],
            'permission: 9, permissionName: WRITE'     => [
                'permissionName'     => 'WRITE',
                'permission'         => 9,
                'grantedExpectation' => false,
            ],
            'permission: 10, permissionName: WRITE'    => [
                'permissionName'     => 'WRITE',
                'permission'         => 10,
                'grantedExpectation' => true,
            ],
            'permission: 11, permissionName: WRITE'    => [
                'permissionName'     => 'WRITE',
                'permission'         => 11,
                'grantedExpectation' => true,
            ],
            'permission: 12, permissionName: WRITE'    => [
                'permissionName'     => 'WRITE',
                'permission'         => 12,
                'grantedExpectation' => false,
            ],
            'permission: 13, permissionName: WRITE'    => [
                'permissionName'     => 'WRITE',
                'permission'         => 13,
                'grantedExpectation' => false,
            ],
            'permission: 14, permissionName: WRITE'    => [
                'permissionName'     => 'WRITE',
                'permission'         => 14,
                'grantedExpectation' => true,
            ],
            'permission: 15, permissionName: WRITE'    => [
                'permissionName'     => 'WRITE',
                'permission'         => 15,
                'grantedExpectation' => true,
            ],
            'permission: 1, permissionName: APPROVE'   => [
                'permissionName'     => 'APPROVE',
                'permission'         => 1,
                'grantedExpectation' => false,
            ],
            'permission: 2, permissionName: APPROVE'   => [
                'permissionName'     => 'APPROVE',
                'permission'         => 2,
                'grantedExpectation' => false,
            ],
            'permission: 3, permissionName: APPROVE'   => [
                'permissionName'     => 'APPROVE',
                'permission'         => 3,
                'grantedExpectation' => false,
            ],
            'permission: 4, permissionName: APPROVE'   => [
                'permissionName'     => 'APPROVE',
                'permission'         => 4,
                'grantedExpectation' => true,
            ],
            'permission: 5, permissionName: APPROVE'   => [
                'permissionName'     => 'APPROVE',
                'permission'         => 5,
                'grantedExpectation' => true,
            ],
            'permission: 6, permissionName: APPROVE'   => [
                'permissionName'     => 'APPROVE',
                'permission'         => 6,
                'grantedExpectation' => true,
            ],
            'permission: 7, permissionName: APPROVE'   => [
                'permissionName'     => 'APPROVE',
                'permission'         => 7,
                'grantedExpectation' => true,
            ],
            'permission: 8, permissionName: APPROVE'   => [
                'permissionName'     => 'APPROVE',
                'permission'         => 8,
                'grantedExpectation' => false,
            ],
            'permission: 9, permissionName: APPROVE'   => [
                'permissionName'     => 'APPROVE',
                'permission'         => 9,
                'grantedExpectation' => false,
            ],
            'permission: 10, permissionName: APPROVE'  => [
                'permissionName'     => 'APPROVE',
                'permission'         => 10,
                'grantedExpectation' => false,
            ],
            'permission: 11, permissionName: APPROVE'  => [
                'permissionName'     => 'APPROVE',
                'permission'         => 11,
                'grantedExpectation' => false,
            ],
            'permission: 12, permissionName: APPROVE'  => [
                'permissionName'     => 'APPROVE',
                'permission'         => 12,
                'grantedExpectation' => true,
            ],
            'permission: 13, permissionName: APPROVE'  => [
                'permissionName'     => 'APPROVE',
                'permission'         => 13,
                'grantedExpectation' => true,
            ],
            'permission: 14, permissionName: APPROVE'  => [
                'permissionName'     => 'APPROVE',
                'permission'         => 14,
                'grantedExpectation' => true,
            ],
            'permission: 15, permissionName: APPROVE'  => [
                'permissionName'     => 'APPROVE',
                'permission'         => 15,
                'grantedExpectation' => true,
            ],
            'permission: 1, permissionName: DELETE'    => [
                'permissionName'     => 'DELETE',
                'permission'         => 1,
                'grantedExpectation' => false,
            ],
            'permission: 2, permissionName: DELETE'    => [
                'permissionName'     => 'DELETE',
                'permission'         => 2,
                'grantedExpectation' => false,
            ],
            'permission: 3, permissionName: DELETE'    => [
                'permissionName'     => 'DELETE',
                'permission'         => 3,
                'grantedExpectation' => false,
            ],
            'permission: 4, permissionName: DELETE'    => [
                'permissionName'     => 'DELETE',
                'permission'         => 4,
                'grantedExpectation' => false,
            ],
            'permission: 5, permissionName: DELETE'    => [
                'permissionName'     => 'DELETE',
                'permission'         => 5,
                'grantedExpectation' => false,
            ],
            'permission: 6, permissionName: DELETE'    => [
                'permissionName'     => 'DELETE',
                'permission'         => 6,
                'grantedExpectation' => false,
            ],
            'permission: 7, permissionName: DELETE'    => [
                'permissionName'     => 'DELETE',
                'permission'         => 7,
                'grantedExpectation' => false,
            ],
            'permission: 8, permissionName: DELETE'    => [
                'permissionName'     => 'DELETE',
                'permission'         => 8,
                'grantedExpectation' => true,
            ],
            'permission: 9, permissionName: DELETE'    => [
                'permissionName'     => 'DELETE',
                'permission'         => 9,
                'grantedExpectation' => true,
            ],
            'permission: 10, permissionName: DELETE'   => [
                'permissionName'     => 'DELETE',
                'permission'         => 10,
                'grantedExpectation' => true,
            ],
            'permission: 11, permissionName: DELETE'   => [
                'permissionName'     => 'DELETE',
                'permission'         => 11,
                'grantedExpectation' => true,
            ],
            'permission: 12, permissionName: DELETE'   => [
                'permissionName'     => 'DELETE',
                'permission'         => 12,
                'grantedExpectation' => true,
            ],
            'permission: 13, permissionName: DELETE'   => [
                'permissionName'     => 'DELETE',
                'permission'         => 13,
                'grantedExpectation' => true,
            ],
            'permission: 14, permissionName: DELETE'   => [
                'permissionName'     => 'DELETE',
                'permission'         => 14,
                'grantedExpectation' => true,
            ],
            'permission: 15, permissionName: DELETE'   => [
                'permissionName'     => 'DELETE',
                'permission'         => 15,
                'grantedExpectation' => true,
            ],
            'permission: 1, permissionName: ANYTHING'  => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 1,
                'grantedExpectation' => false,
            ],
            'permission: 2, permissionName: ANYTHING'  => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 2,
                'grantedExpectation' => false,
            ],
            'permission: 3, permissionName: ANYTHING'  => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 3,
                'grantedExpectation' => false,
            ],
            'permission: 4, permissionName: ANYTHING'  => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 4,
                'grantedExpectation' => false,
            ],
            'permission: 5, permissionName: ANYTHING'  => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 5,
                'grantedExpectation' => false,
            ],
            'permission: 6, permissionName: ANYTHING'  => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 6,
                'grantedExpectation' => false,
            ],
            'permission: 7, permissionName: ANYTHING'  => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 7,
                'grantedExpectation' => false,
            ],
            'permission: 8, permissionName: ANYTHING'  => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 8,
                'grantedExpectation' => false,
            ],
            'permission: 9, permissionName: ANYTHING'  => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 9,
                'grantedExpectation' => false,
            ],
            'permission: 10, permissionName: ANYTHING' => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 10,
                'grantedExpectation' => false,
            ],
            'permission: 11, permissionName: ANYTHING' => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 11,
                'grantedExpectation' => false,
            ],
            'permission: 12, permissionName: ANYTHING' => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 12,
                'grantedExpectation' => false,
            ],
            'permission: 13, permissionName: ANYTHING' => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 13,
                'grantedExpectation' => false,
            ],
            'permission: 14, permissionName: ANYTHING' => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 14,
                'grantedExpectation' => false,
            ],
            'permission: 15, permissionName: ANYTHING' => [
                'permissionName'     => 'ANYTHING',
                'permission'         => 15,
                'grantedExpectation' => false,
            ],
        ];
    }
}
