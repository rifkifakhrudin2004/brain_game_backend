<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use HasFactory;

    protected $table = 'materis'; // Menggunakan nama tabel yang benar

    protected $fillable = [
        'title', 'content',
    ];

    // Relasi ke Questions
    public function questions()
    {
        return $this->hasMany(Question::class, 'materi_id'); // Menyesuaikan foreign key
    }
}
