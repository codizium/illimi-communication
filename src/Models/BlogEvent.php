<?php

namespace Illimi\Communication\Models;

use Codizium\Core\Models\BaseModel;
use Codizium\Core\Models\User;
use Codizium\Core\Traits\BelongsToOrganization;
use Codizium\Core\Traits\HasCuid;

class BlogEvent extends BaseModel
{
    use BelongsToOrganization, HasCuid;

    protected $table = 'illimi_blog_events';

    protected $fillable = [
        'organization_id',
        'blog_post_id',
        'title',
        'description',
        'category',
        'status',
        'starts_at',
        'ends_at',
        'location',
        'max_attendees',
        'allow_rsvp',
        'created_by',
    ];

    protected $casts = [
        'id' => 'string',
        'organization_id' => 'string',
        'blog_post_id' => 'string',
        'created_by' => 'string',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'max_attendees' => 'integer',
        'allow_rsvp' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
