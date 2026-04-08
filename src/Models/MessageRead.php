<?php

namespace Illimi\Communication\Models;

use Codizium\Core\Models\BaseModel;
use Codizium\Core\Models\User;
use Codizium\Core\Traits\BelongsToOrganization;
use Codizium\Core\Traits\HasCuid;

class MessageRead extends BaseModel
{
    use BelongsToOrganization, HasCuid;

    protected $table = 'illimi_message_reads';

    protected $fillable = [
        'organization_id',
        'message_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'id' => 'string',
        'organization_id' => 'string',
        'message_id' => 'string',
        'user_id' => 'string',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
