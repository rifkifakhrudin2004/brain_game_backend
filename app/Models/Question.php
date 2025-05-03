<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $table = 'questions'; // Menggunakan nama tabel yang benar

    protected $fillable = [
        'materi_id', 'question', 'level', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer',
    ];

    // Relasi ke Materi
    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id'); // Menyesuaikan foreign key
    }
}
