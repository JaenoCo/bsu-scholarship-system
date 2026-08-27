<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'scholarship_target_colleges' => 'college_id',
            'scholarship_target_programs' => 'program_id',
            'scholarship_target_tracks' => 'program_track_id',
        ] as $tableName => $targetColumn) {
            Schema::create($tableName, function (Blueprint $table) use ($targetColumn) {
                $table->id();
                $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger($targetColumn);
                $table->timestamps();
                $table->unique(['scholarship_id', $targetColumn]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_target_tracks');
        Schema::dropIfExists('scholarship_target_programs');
        Schema::dropIfExists('scholarship_target_colleges');
    }
};
