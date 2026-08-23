<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_contact_connection_id')->constrained()->cascadeOnDelete();
            $table->string('resource_name');
            $table->string('etag')->nullable();
            $table->string('name');
            $table->string('normalized_phone', 15)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->unique(
                ['google_contact_connection_id', 'resource_name'],
                'gcontacts_connection_resource_unique',
            );
            $table->index(
                ['google_contact_connection_id', 'normalized_phone'],
                'gcontacts_connection_phone_index',
            );
            $table->index(
                ['google_contact_connection_id', 'name'],
                'gcontacts_connection_name_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_contacts');
    }
};
