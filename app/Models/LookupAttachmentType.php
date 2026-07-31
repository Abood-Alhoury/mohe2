<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LookupAttachmentType extends Model
{
    protected $table = 'lookup_attachment_types';
    protected $fillable = ['name'];
}
