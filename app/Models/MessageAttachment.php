<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageAttachment extends Model
{
    protected $fillable = ['message_id', 'path', 'original_name', 'mime_type', 'size', 'kind'];
}
