<?php

use App\Models\User;
use App\Models\Permission;
use Database\Seeders\ChatbotKnowledgeSeeder;

function userWithChatbotPermissions(bool $canTeach = false): User
{
    $user = User::factory()->create();

    $actions = $canTeach ? ['ask', 'teach'] : ['ask'];
    $permissionIds = collect($actions)->map(function (string $action) {
        return Permission::query()->updateOrCreate(
            ['module_name' => 'chatbot', 'action' => $action],
            ['description' => ucfirst($action).' chatbot']
        )->permission_id;
    })->all();

    $user->permissions()->syncWithoutDetaching($permissionIds);

    return $user;
}

test('authenticated users can teach and ask the chatbot', function () {
    $user = userWithChatbotPermissions(canTeach: true);

    $this->actingAs($user)
        ->postJson(route('chatbot.teach'), [
            'question' => 'What is GTU ITR?',
            'answer' => 'GTU ITR is an academic management system.',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Thanks, I learned that answer.']);

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'what is gtu itr',
        ])
        ->assertOk()
        ->assertJson([
            'learned' => true,
            'answer' => 'GTU ITR is an academic management system.',
        ]);
});

test('chatbot asks to be taught when it does not know an answer', function () {
    $user = userWithChatbotPermissions();

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'How do I print a hall ticket?',
        ])
        ->assertOk()
        ->assertJson([
            'learned' => false,
            'matched_question' => null,
        ]);
});

test('guests cannot use chatbot endpoints', function () {
    $this->postJson(route('chatbot.ask'), [
        'question' => 'Hello?',
    ])->assertUnauthorized();
});

test('chatbot answers seeded application help questions', function () {
    $user = userWithChatbotPermissions();
    $this->seed(ChatbotKnowledgeSeeder::class);

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'How do I add a student?',
        ])
        ->assertOk()
        ->assertJson([
            'learned' => true,
            'matched_question' => 'How do I add a student?',
        ])
        ->assertJsonPath('answer', fn (string $answer) => str_contains($answer, 'People > Students'));
});

test('chatbot understands rough phrasing and synonyms', function () {
    $user = userWithChatbotPermissions();
    $this->seed(ChatbotKnowledgeSeeder::class);

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'faculty insert',
        ])
        ->assertOk()
        ->assertJson(['learned' => true])
        ->assertJsonPath('answer', fn (string $answer) => str_contains($answer, 'People > Staff'));
});

test('chatbot answers owner plan and license questions', function () {
    $user = userWithChatbotPermissions();
    $this->seed(ChatbotKnowledgeSeeder::class);

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'How do I manage plans?',
        ])
        ->assertOk()
        ->assertJson([
            'learned' => true,
            'matched_question' => 'How do I manage plans?',
        ])
        ->assertJsonPath('answer', fn (string $answer) => str_contains($answer, 'System > Manage Plans'));

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'client package lock fees',
        ])
        ->assertOk()
        ->assertJson(['learned' => true])
        ->assertJsonPath('answer', fn (string $answer) => str_contains($answer, 'subscription plan'));
});

test('chatbot returns suggestions when answer is unknown', function () {
    $user = userWithChatbotPermissions();
    $this->seed(ChatbotKnowledgeSeeder::class);

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'student scholarship magic workflow',
        ])
        ->assertOk()
        ->assertJson(['learned' => false])
        ->assertJsonPath('suggestions', fn (array $suggestions) => count($suggestions) > 0);
});

test('chatbot can add a knowledge query from chat input', function () {
    $user = userWithChatbotPermissions(canTeach: true);

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'add query: How do I add fees? answer: Go to Fees > Fee Collection and save the payment.',
        ])
        ->assertOk()
        ->assertJson([
            'learned' => true,
            'action' => 'added',
            'matched_question' => 'How do I add fees?',
        ]);

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'how do i add fees',
        ])
        ->assertOk()
        ->assertJson([
            'learned' => true,
            'answer' => 'Go to Fees > Fee Collection and save the payment.',
        ]);
});

test('chatbot can update an existing knowledge query from chat input', function () {
    $user = userWithChatbotPermissions(canTeach: true);

    $this->actingAs($user)
        ->postJson(route('chatbot.teach'), [
            'question' => 'How do I update fees?',
            'answer' => 'Old answer.',
        ])
        ->assertOk();

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'update query: How do I update fees? answer: Open Fees > Receipts and verify the transaction reference.',
        ])
        ->assertOk()
        ->assertJson([
            'learned' => true,
            'action' => 'updated',
            'matched_question' => 'How do I update fees?',
        ]);

    $this->actingAs($user)
        ->postJson(route('chatbot.ask'), [
            'question' => 'how do i update fees',
        ])
        ->assertOk()
        ->assertJson([
            'answer' => 'Open Fees > Receipts and verify the transaction reference.',
        ]);
});
