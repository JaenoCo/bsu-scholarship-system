<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Database-specific date expressions for the few reporting queries that need
 * date parts in SELECT/GROUP BY clauses. Eloquent's whereDate/whereMonth
 * remains preferable whenever a raw expression is not needed.
 */
final class DatabaseDateExpressions
{
    public static function year(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%Y', {$column}) AS INTEGER)",
            'pgsql' => "EXTRACT(YEAR FROM {$column})::integer",
            default => "YEAR({$column})",
        };
    }

    public static function month(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            'pgsql' => "EXTRACT(MONTH FROM {$column})::integer",
            default => "MONTH({$column})",
        };
    }
}
