<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchise_id', 'created_by', 'subject',
        'priority', 'status',
    ];

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function getUnreadCountAttribute(): int
    {
        return $this->messages()->where('is_read', false)->count();
    }
}
