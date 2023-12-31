<?php

namespace App\Model;

use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendanceApprove extends Model
{
    // use BranchTrait;
    protected $table = 'employee_attendance_approve';
    protected $primaryKey = 'employee_attendance_approve_id';

    protected $fillable = [
        'employee_attendance_approve_id', 'employee_id', 'branch_id', 'finger_print_id', 'date', 'in_time', 'out_time', 'working_hour', 'approve_working_hour', 'created_by', 'updated_by'
    ];
}
