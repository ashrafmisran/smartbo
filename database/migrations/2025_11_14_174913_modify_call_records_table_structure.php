<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('call_records', function (Blueprint $table) {
            // Drop the foreign key constraint and pengundi_id column
            $table->dropForeign(['pengundi_id']);
            $table->dropColumn('pengundi_id');
            
            // Add pengundi_ic column
            $table->string('pengundi_ic', 12)->after('user_id');
            
            // Change phone_number to string
            $table->string('phone_number', 20)->change();
            
            // Add called_at timestamp
            $table->timestamp('called_at')->after('notes');
            
            // Add indexes
            $table->index(['pengundi_ic', 'called_at']);
            $table->index(['user_id', 'called_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_records', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['pengundi_ic', 'called_at']);
            $table->dropIndex(['user_id', 'called_at']);
            
            // Drop columns
            $table->dropColumn(['pengundi_ic', 'called_at']);
            
            // Restore original structure
            $table->foreignId('pengundi_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('phone_number')->change();
        });
    }
};
