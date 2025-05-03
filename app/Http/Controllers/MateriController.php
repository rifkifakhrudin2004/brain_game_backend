<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Question;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    // Menampilkan semua materi
    public function index()
    {
        $materi = Materi::all(); // Ambil semua materi
        return response()->json($materi);
    }

    // Menampilkan materi beserta soal yang terkait
    public function show($id)
    {
        $materi = Materi::with('questions')->find($id);
        if (!$materi) {
            return response()->json(['message' => 'Materi tidak ditemukan'], 404);
        }

        return response()->json($materi);
    }

    // Menyimpan materi baru (untuk admin)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $materi = Materi::create([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json($materi, 201);
    }
}
