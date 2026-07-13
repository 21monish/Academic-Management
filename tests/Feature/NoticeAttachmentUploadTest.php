<?php

use App\Models\NoticeAttachment;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

function noticeAttachmentLookupIds(User $user): array
{
    $universityId = DB::table('universities')->insertGetId([
        'name' => 'GTU',
    ], 'university_id');

    $collegeId = DB::table('colleges')->insertGetId([
        'university_id' => $universityId,
        'code' => 'NITR',
        'name' => 'Notice ITR College',
    ], 'college_id');

    $noticeId = DB::table('notices')->insertGetId([
        'college_id' => $collegeId,
        'created_by' => $user->user_id,
        'title' => 'Exam Notice',
        'priority' => 'Normal',
        'audience_type' => 'All',
        'is_published' => true,
        'published_at' => now(),
        'created_at' => now(),
    ], 'notice_id');

    return [$collegeId, $noticeId];
}

test('notice attachment upload stores file path and metadata in database', function () {
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true]
    );
    $user = User::factory()->create(['role_id' => $role->role_id]);
    [, $noticeId] = noticeAttachmentLookupIds($user);

    $response = $this->actingAs($user)->post(route('notices.attachments.store'), [
        'notice_id' => $noticeId,
        'attachment' => UploadedFile::fake()->create('exam-notice.pdf', 128, 'application/pdf'),
    ]);

    $attachment = NoticeAttachment::query()->where('notice_id', $noticeId)->first();

    $response->assertRedirect();
    expect($attachment)->not->toBeNull();
    expect($attachment->file_name)->toBe('exam-notice.pdf');
    expect($attachment->file_url)->toStartWith('uploads/notices/');
    expect($attachment->file_type)->toBe('PDF');
    expect($attachment->file_size_kb)->toBeGreaterThan(0);
    expect(File::exists(public_path($attachment->file_url)))->toBeTrue();

    File::delete(public_path($attachment->file_url));
});
