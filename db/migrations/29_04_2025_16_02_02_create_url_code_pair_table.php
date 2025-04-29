<?php

use App\Core\Database\Interfaces\IMigration;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

return new class implements IMigration {
    public function up(): void
    {
        Manager::schema()->create('url_code_pair', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('code')->unique();
            $table->unsignedInteger('count')->default(0);
        });
    }

    public function down(): void
    {
        Manager::schema()->drop('url_code_pair');
    }
};