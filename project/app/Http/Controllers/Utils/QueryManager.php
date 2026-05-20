<?php

namespace App\Http\Controllers\Utils;

use Illuminate\Support\Facades\DB;

class QueryManager
{
    private static mixed $config = null;
    private static mixed $columns = [];
    private static mixed $operators = [];
    private static mixed $sorting = [];

    private static function initialize(): void
    {
        if (!self::$config) self::$config = config('virtualcolumns');

        if (sizeof(self::$columns) <= 0) {
            self::$columns = !empty(self::$config['columns']) ? self::$config['columns'] : [];
        }

        if (sizeof(self::$sorting) <= 0) {
            self::$sorting = !empty(self::$config['sorting']) ? self::$config['sorting'] : [];
        }

        if (sizeof(self::$operators) <= 0) {
            self::$operators = !empty(self::$config['operators']) ? self::$config['operators'] : [];
        }
    }

    public static function applyConditionToQuery(&$query, $conditions): void
    {
        $groupedConditions = [];
        $limitValue = null;
        foreach ($conditions as $condition) {
            if (preg_match('/^\s*([\w.]+)?\s*(=|!=|>=|<=|>|<|LIKE|NOT LIKE|IN|NOT IN|BETWEEN|NOT BETWEEN|IS NULL|IS NOT NULL|REGEXP|NOT REGEXP|RANGE|SORT|LIMIT)?\s*(.*)$/i', trim($condition), $matches)) {
                $column = trim($matches[1]);
                $operator = strtoupper(trim($matches[2]));
                $value = trim($matches[3], " '");
                if ($operator === 'LIMIT') {
                    $limitValue = (int) $value;
                    continue;
                }
                if (!isset($groupedConditions[$column])) {
                    $groupedConditions[$column] = [];
                }
                $groupedConditions[$column][] = compact('column', 'operator', 'value');
            }
        }
        foreach ($groupedConditions as $column => $columnConditions) {
            $query->where(function ($q) use ($columnConditions) {
                foreach ($columnConditions as $index => $condition) {
                    self::applyWhereCondition($q, $condition, $index > 0);
                }
            });
        }
        if ($limitValue !== null) {
            $query->limit($limitValue);
        }
    }

    private static function applyWhereCondition(&$query, $condition, $useOrWhere = false): void
    {
        self::initialize();
        extract($condition);
        $isMultiple = self::getColumnType($column);
        if ($isMultiple && $operator === '=') {
            $value = str_replace("'", '"', $value);
        }
        $method = $useOrWhere ? 'orWhere' : 'where';
        if ($operator === 'LIMIT') {
            $query->limit((int) $value);
            return;
        }
        if (in_array($operator, ['LIKE', 'NOT LIKE'])) {
            $query->$method($column, $operator, !str_contains($value, '%') ? "%{$value}%" : $value);
        } elseif (in_array($operator, ['IN', 'NOT IN'])) {
            $valuesArray = array_map('trim', explode(',', str_replace(['(', ')', '[', ']', "'"], '', $value)));
            if ($isMultiple) {
                $query->$method(function ($q) use ($column, $valuesArray, $operator) {
                    foreach ($valuesArray as $val) {
                        $q->orWhere($column, $operator === 'IN' ? 'LIKE' : 'NOT LIKE', '%"' . $val . '"%');
                    }
                });
            } else {
                // $query->$method($operator === 'IN' ? 'whereIn' : 'whereNotIn', $column, $valuesArray);
                $query->$method(function ($q) use ($column, $valuesArray, $operator) {
                    foreach ($valuesArray as $val) {
                        $q->orWhere($column, $operator === 'IN' ? '=' : '!=', $val);
                    }
                });
            }
        } elseif (in_array($operator, ['BETWEEN', 'NOT BETWEEN'])) {
            $valuesArray = explode(' AND ', $value);
            if (count($valuesArray) === 2) {
                $query->$method($operator === 'BETWEEN' ? 'whereBetween' : 'whereNotBetween', $column, array_map('trim', $valuesArray));
            }
        } elseif ($operator === 'IS NULL') {
            $query->$method($column, null);
        } elseif ($operator === 'IS NOT NULL') {
            $query->$method($column, '!=', null);
        } elseif ($operator === 'RANGE') {
            $dates = explode(' - ', $value);
            if (count($dates) === 2) {
                $query->$method(function ($q) use ($dates) {
                    $q->whereDate('start_date', '<=', $dates[1])
                        ->whereDate('end_date', '>=', $dates[0]);
                });
            }
        } elseif ($operator === 'SORT') {
            $query->orderBy($column, strtoupper($value));
        } else {
            $query->$method($column, $operator, $value);
        }
    }

    public static function getConditionData($storedQuery): array
    {
        self::initialize();
        $returnCondition = [];
        $conditions = explode(' && ', $storedQuery);
        foreach ($conditions as $condition) {
            if (preg_match('/^\s*([\w.]+)?\s*(=|!=|>=|<=|>|<|LIKE|NOT LIKE|IN|NOT IN|BETWEEN|NOT BETWEEN|IS NULL|IS NOT NULL|REGEXP|NOT REGEXP|RANGE|SORT|LIMIT)?\s*(.*)$/i', trim($condition), $matches)) {
                $column = trim($matches[1]);
                $operator = strtoupper(trim($matches[2]));
                $value = trim($matches[3], " '");
                if($operator === 'LIMIT'){
                    $returnCondition[] = [
                        'column' => 'limit',
                        'columnName' => 'limit',
                        'operator' => "LIMIT",
                        'value' => $value,
                        'secondValue' => $value,
                    ];
                }
                if ($operator === 'SORT') {
                    $sortObject = self::getSortObject($column);
                    $returnCondition[] = [
                        'column' => $sortObject['column'],
                        'columnName' => $column,
                        'operator' => "SORT",
                        'value' => $value,
                        'secondValue' => $value === "asc" ? "Ascending" : "Descending",
                    ];
                } else {
                    $isMultiple = self::getColumnType($column);
                    $columnObject = self::getColumnObject($column);
                    $showValue = "";
                    if (!in_array($operator, ['IS NULL', 'IS NOT NULL']) && $columnObject['is_dependent']) {
                        if (in_array($operator, ['IN', 'NOT IN'])) {
                            $valuesArray = array_map('trim', explode(',', str_replace(['(', ')', '[', ']', "'"], '', $value)));
                            $values = array_map(function($val) use ($columnObject) {
                                return self::getDependentValue(
                                    $columnObject['table_name'],
                                    $columnObject['dependent_column_name'],
                                    $columnObject['dependent_column_id'],
                                    $val
                                );
                            }, $valuesArray);
                            $showValue = ($isMultiple ? '[' : '(') . implode(',', $values) . ($isMultiple ? ']' : ')');
                        } else {
                            $processedValue = str_replace(['(', ')', '[', ']', "'"], '', $value);
                            $showValue = $isMultiple ? '[' : '';
                            $showValue .= self::getDependentValue(
                                $columnObject['table_name'],
                                $columnObject['dependent_column_name'],
                                $columnObject['dependent_column_id'],
                                $processedValue
                            );
                            $showValue .= $isMultiple ? ']' : '';
                        }
                    } elseif (!in_array($operator, ['IS NULL', 'IS NOT NULL'])) {
                        $showValue = ($columnObject['type'] === 'boolean') ? ($value == 0 ? "false" : "true") : $value;
                    } else {
                        $value = "Null";
                        $showValue = "Null";
                    }
                    $returnCondition[] = [
                        'column' => $columnObject['column'] ?? $column,
                        'columnName' => $column,
                        'operator' => $operator,
                        'value' => $value,
                        'secondValue' => $showValue,
                    ];
                }
            }
        }
        return $returnCondition;
    }

    private static function getDependentValue($tableName, $dependentColumnName, $dependentColumnId, $id)
    {
        return DB::table($tableName)
            ->where($dependentColumnId, $id)
            ->value($dependentColumnName);
    }

    public static function getColumnType($columnName)
    {
        foreach (self::$columns as $column) {
            if ($column['column_name'] === $columnName) {
                return $column['isMultiple'] ?? false;
            }
        }
        return null;
    }

    public static function getColumnObject($columnName)
    {
//        self::initialize();
        foreach (self::$columns as $column) {
            if ($column['column_name'] === $columnName) {
                return $column;
            }
        }
        return null;
    }

    public static function getSortObject($columnName)
    {
//        self::initialize();
        foreach (self::$sorting as $column) {
            if ($column['column_name'] === $columnName) {
                return $column;
            }
        }
        return null;
    }


}