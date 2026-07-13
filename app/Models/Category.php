<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    public $timestamps = false;
    protected $guarded = ['category_id'];

    public function students()
    {
        return $this->hasMany(Student::class, 'category_id', 'category_id');
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class, 'student_category_id', 'category_id');
    }
}
