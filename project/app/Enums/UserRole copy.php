<?php

namespace App\Enums;

enum UserRole : string
{
    case ADMIN = 'Admin';
    case MANAGER = 'Manager';
    case SEO_MANAGER = 'SEO Manager';
    case SEO_EXECUTIVE = 'SEO Executive';
    case SEO_INTERN = 'SEO Intern';
    case DESIGNER_MANAGER = 'Designer Manager';
    case DESIGNER_EMPLOYEE = 'Designer Employee';
    case SALES_MANAGER = 'Sales Manager';
    case SALES = 'Sales';

    /**
     * Return ID from enum case
     */
    public function id(): int
    {
        return match($this) {
            self::ADMIN => 1,
            self::MANAGER => 2,
            self::SEO_MANAGER => 4,
            self::SEO_EXECUTIVE => 5,
            self::SEO_INTERN => 6,
            self::DESIGNER_MANAGER => 7,
            self::DESIGNER_EMPLOYEE => 8,
            self::SALES_MANAGER => 9,
            self::SALES => 10,
        };
    }

    /**
     * Return enum based on ID
     */
    public static function getRoleById(int $id): ?UserRole
    {
        return match($id) {
            1 => self::ADMIN,
            2 => self::MANAGER,
            4 => self::SEO_MANAGER,
            5 => self::SEO_EXECUTIVE,
            6 => self::SEO_INTERN,
            7 => self::DESIGNER_MANAGER,
            8 => self::DESIGNER_EMPLOYEE,
            9 => self::SALES_MANAGER,
            10 => self::SALES,
            default => null
        };
    }
}
