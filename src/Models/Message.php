<?php

namespace Illimi\Communication\Models;

use Codizium\Core\Models\BaseModel;
use Codizium\Core\Models\User;
use Codizium\Core\Traits\BelongsToOrganization;
use Codizium\Core\Traits\HasCuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends BaseModel
{
    use BelongsToOrganization, HasCuid, SoftDeletes;

    protected $table = 'illimi_messages';

    protected $fillable = [
        'organization_id',
        'conversation_id',
        'sender_id',
        'body',
        'attachments',
        'is_system_message',
    ];

    protected $casts = [
        'id' => 'string',
        'organization_id' => 'string',
        'conversation_id' => 'string',
        'sender_id' => 'string',
        'attachments' => 'array',
        'is_system_message' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reads()
    {
        return $this->hasMany(MessageRead::class, 'message_id');
    }

    public function deliveries()
    {
        return $this->hasMany(MessageDelivery::class, 'message_id');
    }
}
