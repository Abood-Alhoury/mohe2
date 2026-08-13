<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class WipeSystemDataSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Truncate all application & candidate related data
        DB::table('application_decisions')->truncate();
        DB::table('application_messages')->truncate();
        DB::table('application_courses')->truncate();
        DB::table('education_residences')->truncate();
        DB::table('education_attachments')->truncate();
        DB::table('educations')->truncate();
        DB::table('applications')->truncate();
        DB::table('equivalence_profiles')->truncate();

        Schema::enableForeignKeyConstraints();

        // Clean storage directories for attachments and decisions
        try {
            Storage::disk('public')->deleteDirectory('attachments');
            Storage::disk('public')->deleteDirectory('decisions');
            Storage::disk('public')->makeDirectory('attachments');
            Storage::disk('public')->makeDirectory('decisions');
        } catch (\Throwable $e) {
            // Ignore file deletion warnings if any
        }
    }
}
