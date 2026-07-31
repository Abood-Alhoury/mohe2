<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationAttachment extends Model
{
    protected $table = 'education_attachments';

    protected $fillable = [
        'education_id',
        'attachment_type_id',
        'file_path',
        'notes',
    ];

    public function education()
    {
        return $this->belongsTo(Education::class, 'education_id');
    }

    public function attachmentType()
    {
        return $this->belongsTo(LookupAttachmentType::class, 'attachment_type_id');
    }
}
