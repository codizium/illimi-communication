<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('illimi_notice_posts', function (Blueprint $table) {
            $table->string('category')->nullable()->after('description');
        });

        Schema::table('illimi_blog_events', function (Blueprint $table) {
            $table->string('category')->nullable()->after('description');
            $table->string('status')->default('published')->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('illimi_notice_posts', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('illimi_blog_events', function (Blueprint $table) {
            $table->dropColumn(['category', 'status']);
        });
    }
};
