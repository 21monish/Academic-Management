<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;
    protected $guarded = ['user_id'];

    protected $hidden = ['password_hash', 'reset_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'must_change_password' => 'boolean',
        'last_login' => 'datetime',
        'reset_token_expiry' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Override so Laravel's auth guard checks the right column
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function getIdAttribute()
    {
        return $this->user_id;
    }

    public function getNameAttribute()
    {
        return $this->username;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['username'] = $value;
    }

    public function getPasswordAttribute()
    {
        return $this->password_hash;
    }

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password_hash'] = $value;
    }

    public function getEmailVerifiedAtAttribute()
    {
        return $this->is_verified ? ($this->created_at ?? now()) : null;
    }

    public function setEmailVerifiedAtAttribute($value): void
    {
        $this->attributes['is_verified'] = filled($value);
    }

    public function hasVerifiedEmail(): bool
    {
        return (bool) $this->is_verified;
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill(['is_verified' => true])->save();
    }

    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    public function role()
    {
        return $this->belongsTo(UserRole::class, 'role_id', 'role_id');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'user_id', 'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id');
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id', 'university_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'dept_id');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programme_id', 'programme_id');
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class, 'user_id', 'user_id');
    }

    // Polymorphic: resolves to Staff or Student based on reference_type
    public function reference()
    {
        return $this->reference_type === 'Staff'
            ? Staff::find($this->reference_id)
            : Student::find($this->reference_id);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'reference_id', 'student_id')
            ->where('reference_type', 'Student');
    }
}
