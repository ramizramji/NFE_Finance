<?php

namespace App\Http\Controllers\Payroll;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Model\EmployeeAttendance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{

    public function index(Request $request)
    {

        $attendance = DB::table('salary_details')->join('employee', 'employee.employee_id', '=', 'salary_details.employee_id')->select('salary_details.*', 'employee.first_name', 'employee.last_name')->orderBy('date', 'desc')->paginate(15);
        return view('admin.payroll.generateSalarySheet.generateSalarySheet', ['attendanceList' => $attendance]);
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'select_file' => 'required|mimes:csv,xls,xlsx',
        ]);
        $path = $request->file('select_file')->getRealPath();
        $data = Excel::load($path)->get();

        foreach ($data as $value) {
            $fp   = (int)$value->finger_print_id;
            $time = dateConvertFormtoDB($value->in_out_time) . ' ' . date("H:i:s", strtotime($value->in_out_time));
            if (isset($value->finger_print_id) && isset($value->in_out_time)) {
                $attendance_list[] = ['finger_print_id' => $fp, 'in_out_time' => $time, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()];
            } else {
                return back()->with('danger', 'Column / Heading Not Found!, Please Check the File');
                // break;
            }

        }
        // print_r($value);
        // print_r($attendance_list);
        // print_r($fp);
        // print_r($time);
        if (!empty($attendance_list)) {
            try {
                DB::beginTransaction();
                DB::table('employee_attendance')->insert($attendance_list);
                // \Session::flash('success', 'File improted successfully.');
                DB::commit();
                return back()->with('success', 'Employee attendance information successfully imported.');
            } catch (\Exception $e) {
                DB::rollback();
                $e->getMessage();
                return back()->with('danger', 'Something Went Wrong!, Please try Again.');
            }
        } else {
            return back()->with('danger', 'No Data Found!, Please Check the File');
        }
    }

    public function export($type)
    {
        $attendance = EmployeeAttendance::select('employee_attendance_id', 'finger_print_id', 'in_out_time')->get()->toArray();
        return Excel::create('Employee Attendance', function ($excel) use ($attendance) {
            $excel->sheet('Attendance Details', function ($sheet) use ($attendance) {
                $sheet->fromArray($attendance);
            });
        })->download($type);
    }

}
