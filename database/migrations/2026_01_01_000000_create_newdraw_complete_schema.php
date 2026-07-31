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
        // 1. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

        // 2. Lookup Countries
        Schema::create('lookup_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->timestamps();
        });

        // 3. Lookup Education Levels
        Schema::create('lookup_education_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->timestamps();
        });

        // 4. Lookup Universities
        Schema::create('lookup_universities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('lookup_countries')->nullOnDelete();
            $table->string('name', 255);
            $table->timestamps();
        });

        // 5. Users table (enhanced with university relation and card_status)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('university_id')->nullable()->constrained('lookup_universities')->nullOnDelete();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->boolean('is_active')->default(true);
            $table->string('card_status', 50)->default('normal'); // 'normal', 'yellow_card', 'frozen'
            $table->timestamps();
        });

        // 6. Equivalence Profiles (Candidate Data)
        Schema::create('equivalence_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 255);
            $table->string('national_id', 50)->nullable()->unique();
            $table->date('dob')->nullable();
            $table->string('job_title', 150)->nullable();
            $table->foreignId('nationality_id')->nullable()->constrained('lookup_countries')->nullOnDelete();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('gender', 50)->nullable();
            $table->boolean('is_syrian')->default(true);
            $table->timestamps();
        });

        // 7. Applications
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('equivalence_profiles')->cascadeOnDelete();
            $table->string('application_no', 100)->nullable()->unique();
            $table->string('request_type', 100)->nullable();
            $table->foreignId('work_university_id')->nullable()->constrained('lookup_universities')->nullOnDelete();
            $table->string('work_faculty', 255)->nullable();
            $table->string('work_department', 255)->nullable();
            $table->string('study_system', 100)->nullable();
            $table->boolean('has_previous_degree')->default(false);
            $table->string('status', 100)->default('قيد الدراسة');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 8. Application Courses
        Schema::create('application_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('faculty', 255)->nullable();
            $table->string('department', 255)->nullable();
            $table->string('course_name', 255);
            $table->string('course_status', 100)->nullable();
            $table->date('status_date')->nullable();
            $table->timestamps();
        });

        // 9. Educations
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('education_level_id')->constrained('lookup_education_levels');
            $table->foreignId('country_id')->nullable()->constrained('lookup_countries')->nullOnDelete();
            $table->foreignId('university_id')->nullable()->constrained('lookup_universities')->nullOnDelete();
            $table->string('section_name', 255)->nullable();
            $table->string('general_specialization', 255)->nullable();
            $table->string('exact_specialization', 255)->nullable();
            $table->date('registration_date')->nullable();
            $table->date('grant_date')->nullable();
            $table->date('defense_date')->nullable();
            $table->string('rank', 100)->nullable(); // المرتبة/التقدير
            $table->string('supervisor_name', 255)->nullable();
            $table->text('thesis_title')->nullable();
            $table->string('envoy_decision', 255)->nullable(); // قرار الإيفاد
            $table->date('envoy_date')->nullable();
            $table->integer('experience_from_year')->nullable();
            $table->integer('experience_to_year')->nullable();
            $table->string('study_language', 100)->nullable();
            $table->integer('duration_years')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. Lookup Attachment Types
        Schema::create('lookup_attachment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->timestamps();
        });

        // 11. Education Attachments
        Schema::create('education_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_id')->constrained('educations')->cascadeOnDelete();
            $table->foreignId('attachment_type_id')->constrained('lookup_attachment_types');
            $table->text('file_path');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 12. Education Residences
        Schema::create('education_residences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('education_id')->constrained('educations')->cascadeOnDelete();
            $table->string('page_number', 50)->nullable();
            $table->string('exit_airport', 255)->nullable();
            $table->date('exit_date')->nullable();
            $table->string('entry_airport', 255)->nullable();
            $table->date('entry_date')->nullable();
            $table->text('stamp_details')->nullable();
            $table->timestamps();
        });

        // 13. Site Settings (إعدادات الموقع وإغلاق الموقع)
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 14. Application Messages (إشعارات ورسائل بين المدير والجامعة)
        Schema::create('application_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 15. Application Decisions (إرفاق صور وقرارات التعادل)
        Schema::create('application_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('decision_no', 100)->nullable();
            $table->date('decision_date')->nullable();
            $table->string('file_path', 500);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_decisions');
        Schema::dropIfExists('application_messages');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('education_residences');
        Schema::dropIfExists('education_attachments');
        Schema::dropIfExists('lookup_attachment_types');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('application_courses');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('equivalence_profiles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('lookup_universities');
        Schema::dropIfExists('lookup_education_levels');
        Schema::dropIfExists('lookup_countries');
        Schema::dropIfExists('roles');
    }
};
