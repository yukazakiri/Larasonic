<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/* CREATE  TABLE `laravel-v1`.faculty (
    id                   INT    NOT NULL   PRIMARY KEY,
    first_name           VARCHAR(255)    NOT NULL   ,
    last_name            VARCHAR(255)    NOT NULL   ,
    middle_name          VARCHAR(255)       ,
    email                VARCHAR(255)    NOT NULL   ,
    phone_number         VARCHAR(20)       ,
    department           VARCHAR(11)       ,
    office_hours         VARCHAR(255)       ,
    birth_date           DATE       ,
    address_line1        VARCHAR(255)       ,
    biography            TEXT       ,
    education            TEXT       ,
    courses_taught       TEXT       ,
    photo_url            VARCHAR(255)       ,
    `status`             ENUM('active','inactive','on_leave','retired')       ,
    created_at           TIMESTAMP  DEFAULT (CURRENT_TIMESTAMP)     ,
    updated_at           TIMESTAMP  DEFAULT (CURRENT_TIMESTAMP) ON UPDATE CURRENT_TIMESTAMP    ,
    gender               ENUM('male','female','other')       ,
    age                  INT       ,
    CONSTRAINT email UNIQUE ( email )
 );

 */
final class Faculty extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $table = 'faculty';

    // Add keyType and incrementing properties for UUID
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'first_name', 'last_name', 'middle_name', 'email', 'phone_number', 'department', 'office_hours', 'birth_date', 'address_line1', 'biography', 'education', 'courses_taught', 'photo_url', 'status', 'gender', 'age',
    ];

    // Add casts property for UUID
    protected $casts = [
        'id' => 'string',
    ];

    public function account()
    {
        return $this->hasOne(User::class, 'person_id', 'id');
    }

    public function getFacultyFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->middle_name.' '.$this->last_name;
    }

    public function classes()
    {
        return $this->hasMany(Classes::class, 'faculty_id');
    }

    // get the schedule for the faculty
    public function schedule()
    {
        return $this->hasMany(Schedule::class, 'faculty_id');
    }
}
