<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('causer', 'causer');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->string('log_name')->nullable()->index();
            $table->string('description');
            $table->json('attribute_changes')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }
};
