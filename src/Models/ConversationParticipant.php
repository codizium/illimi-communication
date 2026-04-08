<?php

namespace Illimi\Communication\Models;

use Codizium\Core\Models\BaseModel;
use Codizium\Core\Models\User;
use Codizium\Core\Traits\BelongsToOrganization;
use Codizium\Core\Traits\HasCuid;

class ConversationParticipant extends BaseModel
{
    use BelongsToOrganization, HasCuid;

    protected $table = 'illimi_conversation_participants';

    protected $fillable = [
        'organization_id',
        'conversation_id',
        'user_id',
        'role',
        'joined_at',
        'left_at',
        'is_muted',
    ];

    protected $casts = [
        'id' => 'string',
        'organization_id' => 'string',
        'conversation_id' => 'string',
        'user_id' => 'string',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'is_muted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
