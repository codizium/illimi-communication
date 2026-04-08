<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('illimi_conversation_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('conversation_id')->index();
            $table->string('user_id')->index();
            $table->string('role', 50)->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['conversation_id', 'user_id'], 'illimi_conversation_participants_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('illimi_conversation_participants');
    }
};
