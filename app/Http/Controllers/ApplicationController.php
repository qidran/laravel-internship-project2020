<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationEditRequest;
use App\Http\Requests\ApplicationPostRequest;
use Illuminate\Support\Facades\Auth;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

use Carbon\Carbon;

use App\Models\User;
use App\Models\LeaveDetail;
use App\Models\LeaveApplication;
use App\Models\Holiday;
use App\Models\RefLeaveType;

use App\Traits\LeaveTrait;

class ApplicationController extends Controller
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
        //
    }

    public function list()
    {
        $actives         = LeaveApplication::where('user_id', Auth::id())
                                            ->where('to', '>=', Carbon::today())
                                            ->where('application_status_id','!=',3)
                                            ->paginate(5);

        $pasts           = LeaveApplication::where('user_id', Auth::id())
                                            ->where('to', '<', Carbon::today())
                                            ->where('application_status_id','!=',1)
                                            ->paginate(5);

        return view('application.application_list' , compact('actives', 'pasts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user            = User::find(Auth::id());
        $leave           = LeaveDetail::where('user_id', $user->id)->first();
        $refLeaveTypes   = RefLeaveType::get();
        $holidays        = Holiday::pluck('holiday_date');

        JavaScriptFacade::put([
            'holidays'    => $holidays
        ]);

        return view('application.apply', compact('user','leave','refLeaveTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(ApplicationPostRequest $request)
    {
        $user                    = User::find(Auth::id());
        $leave                   = LeaveDetail::where('user_id', $user->id)->first();

        $from                       = Carbon::parse(Carbon::createFromFormat('d/m/Y', $request->get('from'))->format('Y-m-d'));
        $to                         = Carbon::parse(Carbon::createFromFormat('d/m/Y', $request->get('to'))->format('Y-m-d'));
        $fromDiff                   = ($from->diffInDays(Carbon::today()));
        $days_taken                 = $request->get('days_taken');
        $half_day                   = $request->get('half_day');

        if ($half_day == 1) {
            $days_taken = $days_taken - (0.5);
        }

        $applications_temp          = LeaveApplication::where('user_id', $user->id)->where('application_status_id',1)->sum('days_taken');
        $applications_temp_sum      = $applications_temp + $days_taken;

            if($days_taken<= $leave->balance_leaves)
            {
                if($applications_temp_sum <= $leave->balance_leaves) // Check Current Balance Leaves. If sufficient, proceed.
                {
                    $application                = new LeaveApplication();
                    $application->user_id       = $user->id;
                    $application->leave_id      = $leave->id;
                    $application->leave_type_id = $request->get('leave_type_id');

                    if($application->leave_type_id == 2 || $application->leave_type_id == 3 || $application->leave_type_id == 4 ) //Medical or Emergency or Unrecorded
                    {
                        $application->from                  = $from;
                        $application->to                    = $to;
                        $application->days_taken            = $days_taken;
                        $application->half_day              = $half_day;
                        $application->reason                = $request->get('reason');
                        $application->application_status_id = 1; //Pending Status

                        $application->save();

                        return redirect('/application/list')->with('success', 'Application submitted.');
                    }
                    else
                    {
                        if($days_taken <= 2) //Annual Leave. Check days taken. If days taken <= 2 days, proceed.
                        {
                            if( $fromDiff >= 2)
                            {
                                $application->from                  = $from;
                                $application->to                    = $to;
                                $application->days_taken            = $days_taken;
                                $application->half_day              = $half_day;
                                $application->reason                = $request->get('reason');
                                $application->application_status_id = 1; //Pending Status

                                $application->save();

                                return redirect('/application/list')->with('success', 'Application submitted.');
                            }
                            else // Annual Leave. Days taken <= 2 days, but 1 day before. Error. Must apply 2 days before.
                            {
                                return redirect('/application/apply')->with('error', 'Cannot Apply: Application must be applied 2 days before.');
                            }

                        }
                        else //Annual Leave. More than 2 days, error. Must apply 7 days prior.
                        {
                            if($fromDiff >= 7)
                            {
                                $application->from                  = $from;
                                $application->to                    = $to;
                                $application->days_taken            = $days_taken;
                                $application->half_day              = $half_day;
                                $application->reason                = $request->get('reason');
                                $application->application_status_id = 1; // Pending Status

                                $application->save();

                                return redirect('/application/list')->with('success', 'Application submitted.');
                            }
                            else
                            {
                                return redirect('/application/apply')->with('error', 'Cannot Apply: Application must be applied 7 days before.');
                            }
                        }
                    }

                }
                else// If Pending Applications' Days Taken Exceeds Leave Balance, Error.
                {
                    return redirect('/application/apply')->with('error', 'Cannot Apply: Pending Applications Exceeds Leave Balance!');
                }
            }
            else // If Current Balance Leaves not sufficient, error.
            {
                return redirect('/application/apply')->with('error', 'Cannot Apply: Insufficient Leave Balance!');
            }



    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $application    = LeaveApplication::find($id);
        $created_at     = date('d/m/Y (H:i:s)', strtotime($application->created_at));

        return view('application.application_show', compact('application','created_at'));
    }

        /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function adminAppShow($id)
    {
        $application    = LeaveApplication::find($id);
        $created_at     = date('d/m/Y (H:i:s)', strtotime($application->created_at));

        return view('admin.application_show', compact('application','created_at'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $application = LeaveApplication::find($id);
        $from        = Carbon::parse($application->from)->format('d/m/Y');
        $to          = Carbon::parse($application->to)->format('d/m/Y');
        $holidays    = Holiday::pluck('holiday_date');

        JavaScriptFacade::put([
            'holidays'    => $holidays
        ]);


        return view('application.application_edit', compact('application','from','to'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\ApplicationEditRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ApplicationEditRequest $request, $id)
    {
        $application                =  LeaveApplication::find($id);
        $application->from          = Carbon::parse(Carbon::createFromFormat('d/m/Y', $request->get('from'))->format('Y-m-d'));
        $application->to            = Carbon::parse(Carbon::createFromFormat('d/m/Y', $request->get('to'))->format('Y-m-d'));
        $days_taken                 = $request->get('days_taken');
        $half_day                   = $request->get('half_day');

        if ($half_day == 1) {
            $days_taken = $days_taken - (0.5);
        }

        $application->days_taken    = $days_taken;
        $application->reason        =  $request->get('reason');
        $application->save();

        return redirect('/application/list')->with('success', 'Application updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $application = LeaveApplication::find($id);
        $application->delete();

        return redirect('/application/list')->with('error', 'Application removed.');

    }
}
