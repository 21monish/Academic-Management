<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayCalendar extends Model
{
    protected $table = 'holiday_calendars';
    protected $primaryKey = 'holiday_id';
    public $timestamps = false;
    protected $guarded = ['holiday_id'];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }
}
