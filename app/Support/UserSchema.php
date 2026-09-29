<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserSchema
{
    public static function ensurePasswordPlain(): void
    {
        if (!Schema::hasTable('users') || Schema::hasColumn('users', 'password_plain')) {
            return;
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->string('password_plain', 191)->nullable()->after('password');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (Schema::hasColumn('users', 'password_plain')) {
                return;
            }
            throw $e;
        }
    }
}
