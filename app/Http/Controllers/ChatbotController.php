<?php

namespace App\Http\Controllers;

use App\Models\ChatbotKnowledge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChatbotController extends Controller
{
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $question = trim($validated['question']);
        $normalized = $this->normalize($question);

        if ($commandResponse = $this->handleKnowledgeCommand($request, $question)) {
            return $commandResponse;
        }

        if ($builtinResponse = $this->builtinResponse($normalized)) {
            return $builtinResponse;
        }

        $knowledge = ChatbotKnowledge::query()
            ->where('is_active', true)
            ->where('normalized_question', $normalized)
            ->first();

        if (! $knowledge) {
            $knowledge = $this->findClosestKnowledge($normalized);
        }

        if ($knowledge) {
            $knowledge->increment('hits');

            return response()->json([
                'answer' => $knowledge->answer,
                'learned' => true,
                'matched_question' => $knowledge->question,
                'suggestions' => $this->suggestions($normalized, $knowledge->chatbot_knowledge_id),
            ]);
        }

        return response()->json([
            'answer' => $this->unknownAnswer($normalized),
            'learned' => false,
            'matched_question' => null,
            'suggestions' => $this->suggestions($normalized),
        ]);
    }

    public function teach(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:500'],
            'answer' => ['required', 'string', 'min:2', 'max:4000'],
        ]);

        $question = trim($validated['question']);
        $normalized = $this->normalize($question);

        $knowledge = ChatbotKnowledge::query()->updateOrCreate(
            ['normalized_question' => $normalized],
            [
                'question' => $question,
                'answer' => trim($validated['answer']),
                'created_by' => $request->user()?->user_id,
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Thanks, I learned that answer.',
            'knowledge_id' => $knowledge->chatbot_knowledge_id,
        ]);
    }

    public function forget(Request $request, ChatbotKnowledge $knowledge): JsonResponse
    {
        abort_unless(hasPermission('chatbot.teach'), 403);

        $request->validate([
            'action' => ['required', Rule::in(['deactivate'])],
        ]);

        $knowledge->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Knowledge entry deactivated.']);
    }

    private function builtinResponse(string $normalizedQuestion): ?JsonResponse
    {
        if (in_array($normalizedQuestion, ['help', 'menu', 'options', 'commands', 'what can you do'], true)) {
            return response()->json([
                'answer' => implode("\n", [
                    'I can answer questions about students, staff, attendance, exams, fees, notices, reports, users, roles, and password changes.',
                    'Teachers/admins with chatbot teach permission can teach me using: add question: ... answer: ...',
                    'To change an answer, use: update question: ... answer: ...',
                ]),
                'learned' => true,
                'matched_question' => 'Chatbot help',
                'suggestions' => $this->starterSuggestions(),
            ]);
        }

        if (in_array($normalizedQuestion, ['popular', 'popular questions', 'top questions', 'most asked'], true)) {
            $questions = ChatbotKnowledge::query()
                ->where('is_active', true)
                ->where('hits', '>', 0)
                ->orderByDesc('hits')
                ->limit(5)
                ->pluck('question')
                ->values()
                ->all();

            return response()->json([
                'answer' => $questions
                    ? 'Popular questions: '.implode(', ', $questions).'.'
                    : 'No popular questions yet. Try asking about students, staff, attendance, exams, fees, or notices.',
                'learned' => true,
                'matched_question' => 'Popular questions',
                'suggestions' => $questions ?: $this->starterSuggestions(),
            ]);
        }

        return null;
    }

    private function findClosestKnowledge(string $normalizedQuestion): ?ChatbotKnowledge
    {
        $best = null;
        $bestScore = 0;
        $queryTokens = $this->tokens($normalizedQuestion);

        ChatbotKnowledge::query()
            ->where('is_active', true)
            ->orderByDesc('hits')
            ->limit(500)
            ->get()
            ->each(function (ChatbotKnowledge $knowledge) use ($normalizedQuestion, $queryTokens, &$best, &$bestScore) {
                similar_text($normalizedQuestion, $knowledge->normalized_question, $score);
                $candidateTokens = $this->tokens($knowledge->normalized_question . ' ' . $knowledge->answer);
                $overlap = count(array_intersect($queryTokens, $candidateTokens));
                $coverage = count($queryTokens) > 0 ? ($overlap / count($queryTokens)) * 100 : 0;
                $candidateScore = max($score, $coverage);

                if (str_contains($knowledge->normalized_question, $normalizedQuestion) || str_contains($normalizedQuestion, $knowledge->normalized_question)) {
                    $candidateScore += 20;
                }

                if ($overlap >= 2) {
                    $candidateScore += min(20, $overlap * 4);
                }

                if ($candidateScore > $bestScore) {
                    $best = $knowledge;
                    $bestScore = $candidateScore;
                }
            });

        return $bestScore >= 58 ? $best : null;
    }

    private function handleKnowledgeCommand(Request $request, string $input): ?JsonResponse
    {
        $command = $this->parseKnowledgeCommand($input);

        if (! $command) {
            return null;
        }

        abort_unless(hasPermission('chatbot.teach'), 403);

        $normalizedQuestion = $this->normalize($command['question']);
        $existing = ChatbotKnowledge::query()
            ->where('normalized_question', $normalizedQuestion)
            ->first();

        if ($command['action'] === 'add' && $existing) {
            return response()->json([
                'answer' => 'That query already exists. Use "update query: '.$command['question'].' answer: ..." to change it.',
                'learned' => true,
                'matched_question' => $existing->question,
                'suggestions' => [],
                'action' => 'exists',
            ]);
        }

        if ($command['action'] === 'update' && ! $existing) {
            return response()->json([
                'answer' => 'I could not find that query to update. Use "add query: '.$command['question'].' answer: ..." to create it.',
                'learned' => false,
                'matched_question' => null,
                'suggestions' => $this->suggestions($normalizedQuestion),
                'action' => 'missing',
            ]);
        }

        $knowledge = ChatbotKnowledge::query()->updateOrCreate(
            ['normalized_question' => $normalizedQuestion],
            [
                'question' => $command['question'],
                'answer' => $command['answer'],
                'created_by' => $existing?->created_by ?? $request->user()?->user_id,
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        $verb = $existing ? 'updated' : 'added';

        return response()->json([
            'answer' => "Done, I {$verb} that query.",
            'learned' => true,
            'matched_question' => $knowledge->question,
            'suggestions' => [],
            'action' => $verb,
        ]);
    }

    private function parseKnowledgeCommand(string $input): ?array
    {
        $text = trim($input);
        $patterns = [
            '/^(?<action>add|create|insert|save)\s+(?:query|question)\s*:\s*(?<question>.+?)\s+(?:answer|ans|reply)\s*:\s*(?<answer>.+)$/isu',
            '/^(?<action>update|change|edit)\s+(?:query|question)\s*:\s*(?<question>.+?)\s+(?:answer|ans|reply)\s*:\s*(?<answer>.+)$/isu',
            '/^(?<action>update|change|edit)\s+(?:answer\s+)?for\s+(?<question>.+?)\s+to\s+(?<answer>.+)$/isu',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match($pattern, $text, $matches)) {
                continue;
            }

            $action = mb_strtolower($matches['action']);
            $question = trim($matches['question']);
            $answer = trim($matches['answer']);

            if (mb_strlen($question) < 2 || mb_strlen($answer) < 2) {
                return null;
            }

            return [
                'action' => in_array($action, ['update', 'change', 'edit'], true) ? 'update' : 'add',
                'question' => mb_substr($question, 0, 500),
                'answer' => mb_substr($answer, 0, 4000),
            ];
        }

        return null;
    }

    private function normalize(string $question): string
    {
        $question = mb_strtolower($question);
        $question = str_replace(array_keys($this->synonyms()), array_values($this->synonyms()), $question);
        $question = preg_replace('/[^a-z0-9\s]/u', ' ', $question) ?? $question;
        $question = preg_replace('/\s+/', ' ', $question) ?? $question;

        return trim($question);
    }

    private function tokens(string $text): array
    {
        $words = explode(' ', $this->normalize($text));
        $stopWords = ['a', 'an', 'and', 'are', 'can', 'do', 'for', 'how', 'i', 'in', 'is', 'me', 'my', 'of', 'or', 'the', 'this', 'to'];

        return array_values(array_unique(array_filter($words, fn (string $word) => strlen($word) > 1 && ! in_array($word, $stopWords, true))));
    }

    private function suggestions(string $normalizedQuestion, ?int $excludeId = null): array
    {
        $queryTokens = $this->tokens($normalizedQuestion);
        $suggestions = [];

        ChatbotKnowledge::query()
            ->where('is_active', true)
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->orderByDesc('hits')
            ->limit(300)
            ->get()
            ->each(function (ChatbotKnowledge $knowledge) use ($queryTokens, &$suggestions) {
                $candidateTokens = $this->tokens($knowledge->normalized_question . ' ' . $knowledge->answer);
                $overlap = count(array_intersect($queryTokens, $candidateTokens));

                if ($overlap > 0) {
                    $suggestions[] = [
                        'question' => $knowledge->question,
                        'score' => $overlap,
                    ];
                }
            });

        usort($suggestions, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_values(array_unique(array_column(array_slice($suggestions, 0, 4), 'question')));
    }

    private function starterSuggestions(): array
    {
        return [
            'How do I add a student?',
            'How do I add staff?',
            'How do I mark attendance?',
            'How do I create user account?',
        ];
    }

    private function unknownAnswer(string $normalizedQuestion): string
    {
        $suggestions = $this->suggestions($normalizedQuestion);

        if ($suggestions) {
            return "I am not fully sure yet. Did you mean: " . implode(', ', $suggestions) . '?';
        }

        return "I don't know this yet. Teach me the answer and I will remember it next time.";
    }

    private function synonyms(): array
    {
        return [
            'chat boat' => 'chatbot',
            'chat bot' => 'chatbot',
            'faculty' => 'staff',
            'teacher' => 'staff',
            'professor' => 'staff',
            'add' => 'create',
            'insert' => 'create',
            'new' => 'create',
            'remove' => 'delete',
            'marks' => 'mark',
            'result card' => 'result',
            'fee receipt' => 'receipt',
            'hallticket' => 'hall ticket',
        ];
    }
}
