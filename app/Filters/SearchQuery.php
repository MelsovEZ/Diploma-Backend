<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchQuery
{
    public static function apply(Builder $query, Request $request, array $fields): Builder
    {
        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));

            $query->where(function ($q) use ($search, $fields) {
                foreach ($fields as $index => $field) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $q->{$method}($field, 'ILIKE', "%{$search}%");
                }
            });

            $caseParts = [];
            $bindings = [];

            foreach ($fields as $i => $field) {
                $caseParts[] = "WHEN LOWER($field) LIKE ? THEN " . ($i + 1);
                $bindings[] = "{$search}%";

                $caseParts[] = "WHEN LOWER($field) LIKE ? THEN " . ($i + 1 + count($fields));
                $bindings[] = "%{$search}%";
            }

            $caseSql = "CASE " . implode(" ", $caseParts) . " ELSE " . (count($fields) * 2 + 1) . " END";

            $query->orderByRaw($caseSql, $bindings);
        }

        return $query;
    }
}
