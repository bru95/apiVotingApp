<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    
    protected $fillable = [
        'description',
        'status',
        'code'
    ];

    public function choices()
    {
        return $this->hasMany(Choice::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

}
