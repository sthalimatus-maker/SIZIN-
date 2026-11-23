<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addUsername = ! Schema::hasColumn('users', 'username');
        $addRole = ! Schema::hasColumn('users', 'role');
        $addClassName = ! Schema::hasColumn('users', 'class_name') && ! Schema::hasColumn('users', 'kelas');
        $addPhone = ! Schema::hasColumn('users', 'phone');

        Schema::table('users', function (Blueprint $table) use ($addUsername, $addRole, $addClassName, $addPhone) {
            if ($addUsername) $table->string('username')->nullable()->unique();
            if ($addRole) $table->string('role')->default('siswa')->index();
            if ($addClassName) $table->string('class_name')->nullable();
            if ($addPhone) $table->string('phone')->nullable();
        });
    }

    public function down(): void
    {
        // Keep user profile columns when rolling back: an existing installation
        // may have owned these columns before this compatibility migration ran.
    }
};
