<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        $now = now();

        DB::table('tasks')
            ->whereNotNull('assigned_to')
            ->select('project_id', 'assigned_to')
            ->distinct()
            ->get()
            ->each(fn ($row) => DB::table('project_user')->insert([
                'project_id' => $row->project_id,
                'user_id' => $row->assigned_to,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};
