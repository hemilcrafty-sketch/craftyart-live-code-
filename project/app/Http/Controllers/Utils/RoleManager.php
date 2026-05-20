<?php

namespace App\Http\Controllers\Utils;

use App\Enums\UserRole;
use App\Http\Controllers\AppBaseController;
use App\Models\User;
use App\Models\UserData;

class RoleManager extends AppBaseController
{
    // public static function isAdminOrManager(int $id): bool
    // {
    //     $user = User::find($id);
    //     if (!$user)
    //         return false;
    //     return self::hasRoleName($user->user_type, [UserRole::ADMIN, UserRole::MANAGER]);
    // }

    public static function isAdminOrFenil(int $id): bool
    {
        $user = User::find($id);
        if (!$user)
            return false;

        return $user->user_type === 1 || $id === 40;
    }

    public static function isAdmin(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::ADMIN]);
    }

    public static function isManager(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::MANAGER]);
    }

    // public static function isEmployee(int $userType): bool
    // {
    //     return self::hasRoleName($userType, [UserRole::MANAGER]);
    // }

    public static function getUserType($userType)
    {
        $roles = config('role');
        foreach ($roles as $role) {
            if ((int) $role['id'] === (int) $userType) {
                return $role['role']->value;
            }
        }

        return "Unknown";
    }

    public static function getEmployeeName($id)
    {
        if ($id == 0) {
            return "N/A";
        }
        $res = User::find($id);
        if ($res) {
            return $res->name;
        } else {
            return "N/A";
        }
    }

    public static function getCreatorName($id)
    {
        if (!$id) {
            return "";
        }
        $res = UserData::where('uid', $id)->first();
        if ($res) {
            return $res->name;
        } else {
            return "";
        }
    }

    public static function getUploaderName($id)
    {
        if (isset($id) && $id != null) {
            $user = User::find($id);
            return (isset($user->name) && $user->name != null) ? $user->name : "N/A";
        } else {
            return "N/A";
        }
    }

    public static function hasRoleName(int $userType, array $matchRoles): bool
    {
        $roleName = self::getUserType($userType);
        $roleValues = array_map(function ($role) {
            return is_object($role) && property_exists($role, 'value') ? $role->value : $role;
        }, $matchRoles);
        return in_array($roleName, $roleValues, true);
    }
    
    public static function isAdminOrSeoManager(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::ADMIN, UserRole::SEO_MANAGER, UserRole::MANAGER]);
    }

    public static function isAdminOrDesignerManager(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::ADMIN, UserRole::DESIGNER_MANAGER, UserRole::MANAGER]);
    }

    public static function isAdminOrSeoManagerOrDesignerManager(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::ADMIN, UserRole::DESIGNER_MANAGER, UserRole::SEO_MANAGER, UserRole::MANAGER]);
    }


    public static function onlySeoAccess(int $userType): bool
    {
        return self::hasRoleName($userType, [
            UserRole::ADMIN,
            UserRole::SEO_MANAGER,
            UserRole::SEO_EXECUTIVE,
            UserRole::SEO_INTERN,
            UserRole::MANAGER
        ]);
    }

    public static function onlyDesignerAccess(int $userType): bool
    {
        return self::hasRoleName($userType, [
            UserRole::ADMIN,
            UserRole::DESIGNER_MANAGER,
            UserRole::DESIGNER_EMPLOYEE,
            UserRole::MANAGER
        ]);
    }

    public static function isSeoManager(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::SEO_MANAGER, UserRole::MANAGER]);
    }

    public static function isSeoExecutive(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::SEO_EXECUTIVE, UserRole::MANAGER]);
    }

    public static function isSeoIntern(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::SEO_INTERN, UserRole::MANAGER]);
    }

    public static function isSeoExecutiveOrIntern(int $userType): bool
    {
        return self::hasRoleName($userType, [
            UserRole::SEO_EXECUTIVE,
            UserRole::SEO_INTERN,
            UserRole::MANAGER
        ]);
    }
    public static function isDesignerManager(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::DESIGNER_MANAGER, UserRole::MANAGER]);
    }

    public static function salesHierarchyAccess(int $userType):bool
    {
        return self::hasRoleName($userType, [UserRole::ADMIN, UserRole::MANAGER, UserRole::SALES_MANAGER, UserRole::SALES]);
    }

    public static function isSalesManager(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::SALES_MANAGER]);
    }

    public static function isSalesEmployee(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::SALES]);
    }

    public static function isAdminOrSalesTeamOrManager(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::ADMIN, UserRole::MANAGER, UserRole::SALES_MANAGER, UserRole::SALES]);
    }

    public static function isAdminOrSalesManager(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::ADMIN, UserRole::MANAGER, UserRole::SALES_MANAGER]);
    }

    public static function isAdminSalesManagerOrNisha(int $currentUserId): bool
    {
        $user = User::find($currentUserId);
        if (!$user)
            return false;
        return $user->user_type == UserRole::ADMIN->id() || $user->user_type == UserRole::SALES_MANAGER->id() || $user->user_type == UserRole::MANAGER->id() || $currentUserId === 101 || $currentUserId === 103;
    }

    public static function isAdminOrHr(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::ADMIN, UserRole::HR]);
    }
    public static function isHr(int $userType): bool
    {
        return self::hasRoleName($userType, [UserRole::HR]);
    }
}