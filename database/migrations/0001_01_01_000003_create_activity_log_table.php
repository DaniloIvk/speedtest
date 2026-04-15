<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();

//          Rest of databases
//
//          $table->nullableMorphs('causer', 'causer');
//          $table->nullableMorphs('subject', 'subject');

//          MongoDB doesn't support nullable morphs

            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->index(['causer_type', 'causer_id']);

            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->index(['subject_type', 'subject_id']);

            $table->string('event')->nullable();
            $table->string('log_name')->nullable()->index();
            $table->string('description');
            $table->json('attribute_changes')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }
};
