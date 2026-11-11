<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed dasar sistem: seluruh akun role (Super Admin + Unit 1-4).
     */
    public function run(): void
    {
        $this->call(AdminSeeder::class);
    }
}
