<?php

use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;

function feeQrUser(): User
{
    $role = UserRole::where('role_name', 'Super Admin')->firstOrFail();

    return User::factory()->create(['role_id' => $role->role_id]);
}

function feeQrLedgerId(): int
{
    $universityId = DB::table('universities')->insertGetId([
        'name' => 'QR Pay University',
        'upi_id' => 'university@upi',
        'upi_name' => 'QR Pay University',
        'upi_note_prefix' => 'University Fee',
    ], 'university_id');
    $collegeId = DB::table('colleges')->insertGetId([
        'university_id' => $universityId,
        'code' => 'QRC',
        'name' => 'QR College',
    ], 'college_id');
    $departmentId = DB::table('departments')->insertGetId([
        'college_id' => $collegeId,
        'code' => 'QRD',
        'name' => 'QR Department',
    ], 'dept_id');
    $programmeId = DB::table('programmes')->insertGetId([
        'dept_id' => $departmentId,
        'code' => 'QRPROG',
        'name' => 'QR Programme',
        'level' => 'UG',
    ], 'programme_id');
    $academicYearId = DB::table('academic_years')->insertGetId([
        'college_id' => $collegeId,
        'label' => '2026-27',
        'status' => 'Active',
    ], 'academic_year_id');
    $semesterId = DB::table('semesters')->insertGetId([
        'programme_id' => $programmeId,
        'semester_no' => 1,
        'academic_year' => '2026-27',
    ], 'semester_id');
    $feeCategoryId = DB::table('fee_categories')->insertGetId([
        'name' => 'QR Tuition',
        'fee_type' => 'Academic',
    ], 'fee_category_id');
    $feeStructureId = DB::table('fee_structures')->insertGetId([
        'college_id' => $collegeId,
        'programme_id' => $programmeId,
        'academic_year_id' => $academicYearId,
        'semester_id' => $semesterId,
        'fee_category_id' => $feeCategoryId,
        'amount' => 1500,
    ], 'fee_structure_id');
    $studentId = DB::table('students')->insertGetId([
        'college_id' => $collegeId,
        'programme_id' => $programmeId,
        'enrollment_no' => 'QR001',
        'first_name' => 'Pay',
        'last_name' => 'Student',
    ], 'student_id');

    return DB::table('student_fee_ledgers')->insertGetId([
        'student_id' => $studentId,
        'fee_structure_id' => $feeStructureId,
        'academic_year_id' => $academicYearId,
        'semester_id' => $semesterId,
        'total_amount' => 1500,
        'net_payable' => 1500,
        'balance_due' => 1500,
    ], 'ledger_id');
}

test('fee collection page generates upi qr data for ledger', function () {
    $this->seed(RolePermissionSeeder::class);

    $ledgerId = feeQrLedgerId();
    $user = feeQrUser();

    $this->actingAs($user)
        ->get(route('fees.collections', ['qr_ledger_id' => $ledgerId, 'qr_amount' => '1234.50']))
        ->assertOk()
        ->assertSee('UPI QR Payment')
        ->assertSee('api.qrserver.com')
        ->assertSee('upi%3A%2F%2Fpay', false)
        ->assertSee('university%40upi', false)
        ->assertSee('QR Pay University')
        ->assertSee('QR001')
        ->assertSee('1,234.50');
});
