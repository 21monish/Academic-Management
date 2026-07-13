<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'student_id';
    public $timestamps = false;
    protected $guarded = ['student_id'];
    protected $casts = [
        'dob' => 'date',
        'admission_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id', 'programme_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function userAccount()
    {
        return $this->hasOne(User::class, 'reference_id', 'student_id')
            ->where('reference_type', 'Student');
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'student_id', 'student_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'student_id', 'student_id');
    }

    public function backlogs()
    {
        return $this->hasMany(Backlog::class, 'student_id', 'student_id');
    }

    public function promotions()
    {
        return $this->hasMany(StudentPromotion::class, 'student_id', 'student_id');
    }

    public function hallTickets()
    {
        return $this->hasMany(HallTicket::class, 'student_id', 'student_id');
    }

    public function feeLedgers()
    {
        return $this->hasMany(StudentFeeLedger::class, 'student_id', 'student_id');
    }

    public function scholarships()
    {
        return $this->hasMany(Scholarship::class, 'student_id', 'student_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id', 'student_id');
    }
}
