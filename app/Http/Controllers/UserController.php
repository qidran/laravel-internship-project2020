<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

use App\Models\User;
use App\Models\LeaveDetail;
use App\Models\LeaveApplication;
use App\Traits\LeaveTrait;

class UserController extends Controller
{
        use LeaveTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->checkPendingApplication();
        $this->yearlyCarryOver(Auth::id());

        $array              = $this->dashboardEmployees(Auth::id());
        $user               = $array['user'];
        $applications_count = $array['applications_count'];
        $applications_count_this_year = $array['applications_count_this_year'];
        $approvers_pending_count = $array['approvers_pending_count'];

        return view('user.dashboard', compact('user' , 'applications_count','applications_count_this_year','approvers_pending_count'));
    }

        /**
     * Display a listing of the pending applications.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendinglist($id)
    {
        $approver   = User::find($id);
        $pendings   = LeaveApplication::join('users','leave_applications.user_id','=','users.id')
                                        ->join('employee_details','users.id','=','employee_details.user_id')
                                        ->select('employee_details.*','users.*','leave_applications.*','leave_applications.id as application_id')
                                        ->where('employee_details.approver_id', $approver->id)
                                        ->where('application_status_id', 1)
                                        ->paginate(5);

        return view('user.pending_list', compact('pendings'));
    }

        /**
     * Display a listing of the applicants list.
     *
     * @return \Illuminate\Http\Response
     */
    public function applicantlist($id)
    {
        $approver = User::find($id);
        $users = User::join('employee_details','employee_details.user_id','=','users.id')
                                    ->where('approver_id' , $approver->id )
                                    ->paginate(10);

        return view('user.applicant_list', compact('users'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $array                 = $this->employeeShow($id);
        $user                  = $array['user'];
        $leave                 = $array['leave'];
        $current_approver_name = $array['current_approver_name'];

        return view('user.employee_detail' , compact('user','leave','current_approver_name'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $app_id
     * @return \Illuminate\Http\Response
     */
    public function approve($app_id)
    {
        // dd($app_id);
        $application                           = LeaveApplication::find($app_id); //$app_id is Application ID
        $application->application_status_id    = 2;
        $application->approval_date            = Carbon::now();
        $application->save();

        if ($application->leave_type_id != 2 &&  $application->leave_type_id != 4) { //Except Medical and Unrecorded Leave
            $leave                                 = LeaveDetail::find($application->leave_id);
            $new_balance_leaves                    = ($leave->balance_leaves)-($application->days_taken);
            $leave->balance_leaves                 = $new_balance_leaves;
            $leave->taken_so_far                   += $application->days_taken;

            $leave->save();
        }

        return redirect(url()->previous())->with('success', 'Application approved.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $app_id
     * @return \Illuminate\Http\Response
     */
    public function reject($app_id)
    {
        $application                        = LeaveApplication::find($app_id);
        $application->application_status_id = 3;
        $application->approval_date         = Carbon::now();
        $application->save();

        return redirect(url()->previous())->with('error', 'Application rejected.');
    }

}
