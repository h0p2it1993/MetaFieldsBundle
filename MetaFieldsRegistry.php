<?php

/*
 * This file is part of the MetaFieldsBundle for Kimai 2.
 * All rights reserved by Kevin Papst (www.kevinpapst.de).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\MetaFieldsBundle;

use App\Entity\Activity;
use App\Entity\ActivityMeta;
use App\Entity\Customer;
use App\Entity\CustomerMeta;
use App\Entity\Project;
use App\Entity\ProjectMeta;
use App\Entity\Timesheet;
use App\Entity\TimesheetMeta;
use App\Entity\User;
use App\Entity\UserPreference;

final class MetaFieldsRegistry
{
    // All ENTITY_* constants represent the value of the database column "entity_type"
    public const EXPENSE_ENTITY = 'KimaiPlugin\ExpensesBundle\Entity\Expense';
    public const TIMESHEET_ENTITY = Timesheet::class;
    public const CUSTOMER_ENTITY = Customer::class;
    public const PROJECT_ENTITY = Project::class;
    public const ACTIVITY_ENTITY = Activity::class;
    public const USER_ENTITY = User::class;

    // All CLASS_* constants represent the value of the real entity class
    public const EXPENSE_CLASS = 'KimaiPlugin\ExpensesBundle\Entity\Expense';
    public const TIMESHEET_CLASS = Timesheet::class;
    public const CUSTOMER_CLASS = Customer::class;
    public const PROJECT_CLASS = Project::class;
    public const ACTIVITY_CLASS = Activity::class;
    public const USER_CLASS = User::class;

    // External mapping names for forms and request parameter
    public const EXPENSE = 'expense';
    public const TIMESHEET = 'timesheet';
    public const CUSTOMER = 'customer';
    public const PROJECT = 'project';
    public const ACTIVITY = 'activity';
    public const USER = 'user';

    public const EXPENSE_META = 'KimaiPlugin\ExpensesBundle\Entity\ExpenseMeta';
    public const TIMESHEET_META = TimesheetMeta::class;
    public const CUSTOMER_META = CustomerMeta::class;
    public const PROJECT_META = ProjectMeta::class;
    public const ACTIVITY_META = ActivityMeta::class;
    public const USER_META = UserPreference::class;

    private const EXTERNAL_TO_ENTITY = [
        self::TIMESHEET => self::TIMESHEET_ENTITY,
        self::CUSTOMER => self::CUSTOMER_ENTITY,
        self::PROJECT => self::PROJECT_ENTITY,
        self::ACTIVITY => self::ACTIVITY_ENTITY,
        self::USER => self::USER_ENTITY,
        self::EXPENSE => self::EXPENSE_ENTITY,
    ];

    private const ENTITY_TO_META = [
        self::TIMESHEET_ENTITY => self::TIMESHEET_META,
        self::CUSTOMER_ENTITY => self::CUSTOMER_META,
        self::PROJECT_ENTITY => self::PROJECT_META,
        self::ACTIVITY_ENTITY => self::ACTIVITY_META,
        self::USER_ENTITY => self::USER_META,
        self::EXPENSE_ENTITY => self::EXPENSE_META,
    ];

    private const ENTITY_TO_TITLE = [
        self::TIMESHEET_ENTITY => 'menu.admin_timesheet',
        self::CUSTOMER_ENTITY => 'label.customer',
        self::PROJECT_ENTITY => 'label.project',
        self::ACTIVITY_ENTITY => 'label.activity',
        self::USER_ENTITY => 'label.user',
        self::EXPENSE_ENTITY => 'Expenses',
    ];

    public static function mapExternalNameToEntityType(string $external): string
    {
        if (!isset(self::EXTERNAL_TO_ENTITY[$external])) {
            throw new \InvalidArgumentException('Unknown entity type by external: ' . $external);
        }

        return self::EXTERNAL_TO_ENTITY[$external];
    }

    public static function mapEntityTypeToExternalName(string $entity): string
    {
        $values = array_flip(self::EXTERNAL_TO_ENTITY);
        if (!isset($values[$entity])) {
            throw new \InvalidArgumentException('Unknown external name by entity type: ' . $entity);
        }

        return $values[$entity];
    }

    public static function getAllExternalNames(): array
    {
        return array_keys(self::EXTERNAL_TO_ENTITY);
    }

    public static function getAllEntityTypes(): array
    {
        return array_keys(self::ENTITY_TO_META);
    }

    public static function mapEntityTypeToMetaClass(string $entityType): string
    {
        if (!isset(self::ENTITY_TO_META[$entityType])) {
            throw new \InvalidArgumentException('Unknown entity type for meta: ' . $entityType);
        }

        return self::ENTITY_TO_META[$entityType];
    }

    public static function mapEntityTypeToTitle(string $entityType): string
    {
        if (!isset(self::ENTITY_TO_TITLE[$entityType])) {
            throw new \InvalidArgumentException('Unknown entity type for title: ' . $entityType);
        }

        return self::ENTITY_TO_TITLE[$entityType];
    }
}
