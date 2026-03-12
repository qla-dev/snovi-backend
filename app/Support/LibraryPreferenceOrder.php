<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LibraryPreferenceOrder
{
    public static function preferredCategoryIds(Request $request): array
    {
        return self::parseIds($request->query('preferred_category_ids', []));
    }

    public static function preferredSubcategoryIds(Request $request): array
    {
        return self::parseIds($request->query('preferred_subcategory_ids', []));
    }

    public static function applyIdPriority(Builder $query, string $column, array $ids): Builder
    {
        if (!$ids) {
            return $query;
        }

        $clauses = [];
        $bindings = [];

        foreach (array_values($ids) as $index => $id) {
            $clauses[] = "WHEN {$column} = ? THEN {$index}";
            $bindings[] = $id;
        }

        return $query->orderByRaw(
            'CASE ' . implode(' ', $clauses) . ' ELSE ' . count($ids) . ' END',
            $bindings,
        );
    }

    public static function applyNullableSort(Builder $query, string $sortColumn, string $labelColumn): Builder
    {
        return $query
            ->orderByRaw("CASE WHEN {$sortColumn} IS NULL THEN 1 ELSE 0 END")
            ->orderBy($sortColumn)
            ->orderBy($labelColumn);
    }

    /**
     * @param  mixed  $value
     * @return array<int>
     */
    private static function parseIds($value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        } elseif (!is_array($value)) {
            $value = [$value];
        }

        $ids = array_map(
            static fn ($item) => (int) $item,
            array_filter($value, static fn ($item) => is_numeric($item) && (int) $item > 0),
        );

        return array_values(array_unique($ids));
    }
}
