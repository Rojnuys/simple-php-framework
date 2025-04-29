<?php

namespace App\Core\Database\Interfaces;

interface IMigration
{
    public function up(): void;
    public function down(): void;
}