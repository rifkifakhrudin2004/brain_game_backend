<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Get questions by materi and optional level
     */
    public function index(Request $request, $materiId)
    {
        $validator = Validator::make($request->all(), [
            'level' => 'sometimes|in:easy,medium,hard'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $questions = Question::where('materi_id', $materiId)
                ->when($request->has('level'), function ($query) use ($request) {
                    return $query->where('level', $request->level);
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => $questions
            ]);

        } catch (\Exception $e) {
            Log::error('Question index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve questions'
            ], 500);
        }
    }

    /**
     * Create new question (admin only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'materi_id' => 'required|exists:materis,id',
            'question' => 'required|string|max:1000',
            'level' => 'required|in:easy,medium,hard',
            'option_a' => 'required|string|max:255',
            'option_b' => 'required|string|max:255',
            'option_c' => 'required|string|max:255',
            'option_d' => 'required|string|max:255',
            'correct_answer' => 'required|string|in:A,B,C,D',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $question = Question::create($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $question
            ], 201);

        } catch (\Exception $e) {
            Log::error('Question store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create question'
            ], 500);
        }
    }

    /**
     * Get questions grouped by level for a materi
     */
    public function getByMateri($materiId)
    {
        try {
            $questions = Question::where('materi_id', $materiId)
                ->get()
                ->groupBy('level');

            return response()->json([
                'success' => true,
                'data' => $questions
            ]);

        } catch (\Exception $e) {
            Log::error('Get by materi error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve questions'
            ], 500);
        }
    }

    /**
     * Get questions by materi and specific level
     */
    public function getByMateriAndLevel($materiId, $level)
    {
        try {
            $questions = Question::where('materi_id', $materiId)
                ->where('level', $level)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $questions
            ]);

        } catch (\Exception $e) {
            Log::error('Get by materi and level error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve questions'
            ], 500);
        }
    }

    /**
     * Submit quiz answers and calculate score
     */
    public function submitQuiz(Request $request, $materiId)
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|in:easy,medium,hard',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'required|string|in:A,B,C,D',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $validated = $validator->validated();
            $correctAnswers = 0;

            // TAMBAHKAN LOGGING
            Log::info('Submit Quiz - User ID: ' . $user->id . ', Materi ID: ' . $materiId);
            Log::info('Answers submitted: ' . json_encode($validated['answers']));

            // Preload all questions at once for better performance
            $questionIds = collect($validated['answers'])->pluck('question_id');
            $questions = Question::whereIn('id', $questionIds)
                ->get()
                ->keyBy('id');
            Log::info('Questions loaded: ' . $questions->pluck('id', 'correct_answer'));

            foreach ($validated['answers'] as $answer) {
                if (isset($questions[$answer['question_id']])) {
                    $question = $questions[$answer['question_id']];
                    // TAMBAHKAN LOGGING UNTUK SETIAP PERBANDINGAN
                $isCorrect = trim(strtoupper($question->correct_answer)) === trim(strtoupper($answer['answer']));
                Log::info('Question ID: ' . $question->id . 
                         ', User answer: "' . $answer['answer'] . 
                         '", Correct answer: "' . $question->correct_answer . 
                         '", Match: ' . ($isCorrect ? 'YES' : 'NO'));
                
                // GUNAKAN PERBANDINGAN YANG LEBIH ROBUST
                if ($isCorrect) {
                    $correctAnswers++;
                }
                }
            }

            $totalQuestions = count($validated['answers']);
            $percentage = round(($correctAnswers / $totalQuestions) * 100);

            // Save score with additional validation
            $score = Score::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'materi_id' => $materiId,
                    'level' => $validated['level']
                ],
                ['score' => $correctAnswers]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'score' => $correctAnswers,
                    'total_questions' => $totalQuestions,
                    'percentage' => $percentage,
                    'score_details' => $score
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Quiz submit error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process quiz submission'
            ], 500);
        }
    }
}