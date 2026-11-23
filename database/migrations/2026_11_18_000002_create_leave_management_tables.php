<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('destination');
            $table->text('reason');
            $table->date('leave_date');
            $table->time('leave_time');
            $table->date('return_date');
            $table->time('return_time');
            $table->string('evidence_path')->nullable();
            $table->string('status')->default('menunggu')->index();
            $table->text('rejection_reason')->nullable();
            $table->dateTime('actual_exit_at')->nullable();
            $table->dateTime('actual_return_at')->nullable();
            $table->text('exit_note')->nullable();
            $table->text('return_note')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel')->default('whatsapp');
            $table->string('recipient');
            $table->text('message');
            $table->string('status')->default('menunggu');
            $table->text('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('leave_status_histories');
        Schema::dropIfExists('leave_requests');
    }
};
