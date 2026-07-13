<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallTicket extends Model
{
    protected $table = 'hall_tickets';
    protected $primaryKey = 'hall_ticket_id';
    public $timestamps = false;
    protected $guarded = ['hall_ticket_id'];

    public function config()
    {
        return $this->belongsTo(HallTicketConfig::class, 'config_id', 'config_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'enrollment_id', 'enrollment_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }

    public function subjects()
    {
        return $this->hasMany(HallTicketSubject::class, 'hall_ticket_id', 'hall_ticket_id');
    }

    public function seatingArrangements()
    {
        return $this->hasMany(SeatingArrangement::class, 'hall_ticket_id', 'hall_ticket_id');
    }

    // Sync helpers mirroring the source spec's cross-module business rules.
    // These are app-level checks, not DB foreign keys.
    public function syncAttendanceCleared(float $threshold): void
    {
        $summary = AttendanceSummary::where('student_id', $this->student_id)->avg('attendance_percentage');
        $this->attendance_cleared = $summary !== null && $summary >= $threshold;
    }

    public function syncFeesCleared(): void
    {
        $this->fees_cleared = StudentFeeLedger::where('student_id', $this->student_id)
            ->where('is_hall_ticket_cleared', false)
            ->doesntExist();
    }
}
