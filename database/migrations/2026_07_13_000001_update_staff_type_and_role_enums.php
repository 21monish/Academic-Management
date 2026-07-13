<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE staff MODIFY staff_type ENUM('Teaching','Non-Teaching','Both') NOT NULL");
        DB::statement("ALTER TABLE teaching_staff MODIFY designation ENUM('Professor','Associate Professor','Assistant Professor','Lecturer','Visiting Faculty','Lab Instructor') NULL");
        DB::statement("ALTER TABLE non_teaching_staff MODIFY `role` ENUM('Peon','Lab Assistant','Lab Technician','Librarian','Accountant','Clerk','Office Assistant','Transport Staff','Security Staff') NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE staff SET staff_type = 'Teaching' WHERE staff_type = 'Both'");
        DB::statement("UPDATE teaching_staff SET designation = NULL WHERE designation IN ('Visiting Faculty','Lab Instructor')");
        DB::statement("UPDATE non_teaching_staff SET `role` = NULL WHERE `role` IN ('Lab Technician','Librarian','Office Assistant','Transport Staff','Security Staff')");

        DB::statement("ALTER TABLE staff MODIFY staff_type ENUM('Teaching','Non-Teaching') NOT NULL");
        DB::statement("ALTER TABLE teaching_staff MODIFY designation ENUM('Professor','Associate Professor','Assistant Professor','Lecturer') NULL");
        DB::statement("ALTER TABLE non_teaching_staff MODIFY `role` ENUM('Lab Assistant','Clerk','Peon','Accountant') NULL");
    }
};
