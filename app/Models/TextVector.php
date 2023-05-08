<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextVector extends Model
{
    use HasFactory;

    protected $fillable = [
        'vector' , 'text_id' , 'file_id'
    ];
}
