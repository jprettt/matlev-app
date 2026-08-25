<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_id',
        'type',
        'title',
        'message',
        'document_id',
        'request_id',
        'target_url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function document()
    {
        return $this->belongsTo(EvidenceUpload::class, 'document_id');
    }

    public function request()
    {
        return $this->belongsTo(DocumentPermissionRequest::class, 'request_id');
    }
}