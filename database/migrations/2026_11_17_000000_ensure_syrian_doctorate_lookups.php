<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\LookupCountry;
use App\Models\LookupUniversity;
use App\Models\LookupAttachmentType;

return new class extends Migration
{
    public function up(): void
    {
        $syria = LookupCountry::firstOrCreate(['name' => 'سوريا']);

        $syrianPhdUniversities = [
            'جامعة دمشق',
            'جامعة حلب',
            'جامعة حمص',
            'جامعة اللاذقية',
            'جامعة طرطوس',
            'جامعة الفرات',
            'الجامعة الافتراضية السورية',
            'جامعة حماة',
            'جامعة إدلب',
        ];

        foreach ($syrianPhdUniversities as $uniName) {
            LookupUniversity::firstOrCreate(
                ['name' => $uniName],
                ['country_id' => $syria->id]
            );
        }

        $newAttachmentTypes = [
            26 => 'قرار الجامعة بالتسجيل بدرجة الدكتوراه',
            27 => 'قرار الجامعة بالمناقشة للدكتوراه',
            28 => 'قرار الجامعة بالحصول على درجة الدكتوراه',
        ];

        foreach ($newAttachmentTypes as $id => $name) {
            $existingByName = LookupAttachmentType::where('name', $name)->first();
            if ($existingByName) {
                continue;
            }

            if (!LookupAttachmentType::where('id', $id)->exists()) {
                DB::table('lookup_attachment_types')->insert([
                    'id'         => $id,
                    'name'       => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                LookupAttachmentType::create(['name' => $name]);
            }
        }
    }

    public function down(): void
    {
        // Intentional no-op to avoid breaking foreign key references in existing records.
    }
};
