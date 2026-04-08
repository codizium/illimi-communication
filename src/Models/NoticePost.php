<?php

namespace Illimi\Communication\Models;

use Codizium\Core\Models\BaseModel;
use Codizium\Core\Models\User;
use Codizium\Core\Traits\BelongsToOrganization;
use Codizium\Core\Traits\HasCuid;

class NoticePost extends BaseModel
{
    use BelongsToOrganization, HasCuid;

    protected $table = 'illimi_notice_posts';

    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'published_at',
        'is_pinned',
        'created_by',
    ];

    protected $casts = [
        'id' => 'string',
        'organization_id' => 'string',
        'created_by' => 'string',
        'published_at' => 'datetime',
        'is_pinned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
