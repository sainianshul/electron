<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public const TYPE_USER = 'user';
    public const TYPE_PRODUCT = 'product';

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'body',
        'created_by',
    ];

    /**
     * Get the parent commentable model.
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the comment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
