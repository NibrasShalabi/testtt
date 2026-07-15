<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $table = 'alerts';
    protected $primaryKey = 'AlertID';

    // المسميات المخصصة للوقت لتطابق الداتابيز
    const CREATED_AT = 'Creat_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'Address',
        'Message',
        'UserID',
        'IsRead',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'id');
    }
}
