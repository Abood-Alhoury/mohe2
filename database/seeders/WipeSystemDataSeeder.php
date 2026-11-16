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

        // Truncate all application, decision, archive & candidate related tables
        $tables = [
            'application_decision_drafts',
            'application_decisions',
            'application_experiences',
            'application_messages',
            'application_courses',
            'education_residences',
            'education_attachments',
            'educations',
            'applications',
            'equivalence_profiles',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        // Reset SQLite auto-increment sequences for wiped tables (if using SQLite)
        if (DB::getDriverName() === 'sqlite') {
            DB::table('sqlite_sequence')->whereIn('name', $tables)->delete();
        }

        Schema::enableForeignKeyConstraints();

        // Clean storage directories for attachments, decisions, test_docs, and temp PDFs
        try {
            Storage::disk('public')->deleteDirectory('attachments');
            Storage::disk('public')->deleteDirectory('decisions');
            Storage::disk('public')->deleteDirectory('test_docs');

            Storage::disk('public')->makeDirectory('attachments');
            Storage::disk('public')->makeDirectory('decisions');

            // Remove any leftover temporary PDF files in storage/app
            foreach (glob(storage_path('app/tmp_*.pdf')) ?: [] as $tmpFile) {
                @unlink($tmpFile);
            }
        } catch (\Throwable $e) {
            // Ignore file deletion warnings if any
        }
    }
}
