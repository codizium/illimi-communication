<?php

namespace Illimi\Communication\Models;

use Codizium\Core\Models\BaseModel;
use Codizium\Core\Models\User;
use Codizium\Core\Traits\BelongsToOrganization;
use Codizium\Core\Traits\HasCuid;

class Conversation extends BaseModel
{
    use BelongsToOrganization, HasCuid;

    protected $table = 'illimi_conversations';

    protected $fillable = [
        'organization_id',
        'type',
        'title',
        'created_by',
        'is_archived',
        'last_message_at',
    ];

    protected $casts = [
        'id' => 'string',
        'organization_id' => 'string',
        'created_by' => 'string',
        'is_archived' => 'boolean',
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latestOfMany('created_at');
    }
}
