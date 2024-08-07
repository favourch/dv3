<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use DB;
use App\Http\Controllers\Controller as BaseController;
use App\Http\Resources\BillingResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BillingTransaction;
use App\Models\Chat;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Students;
use App\Models\ActivityLog;
use App\Helpers\CurrencyHelper;

class DashboardController extends BaseController
{
    public function index(Request $request)
    {
        $duration = $request->query('duration', 7);

        $billingRows = BillingTransaction::whereHas('organization', function ($query) {
            $query->whereNull('deleted_at');
        })
            ->with('organization')
            ->limit(5)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue = BillingTransaction::whereHas('organization', function ($query) {
            $query->whereNull('deleted_at');
        })->where('entity_type', '=', 'payment')->sum('amount');
        $data['totalRevenue'] = CurrencyHelper::formatCurrency($totalRevenue);

        $staticNumberMessages = 15850;
        $staticNumberAPI = 1164000;
        $staticNumberUsers = 25000;
        $staticNumberStudents = 0;
        $staticNumberSubjects = 18;
        $staticNumberStates = 36;
        $staticNumberClasses = 6;
        $staticRevenue = 600;

        $data['userCount'] = User::where('role', '=', 'user')->where('deleted_at', NULL)->count() + $staticNumberUsers;
        $data['studentCount'] = Students::count() + $staticNumberStudents;
        $data['openTickets'] = Ticket::whereHas('user', function ($query) {
            $query->whereNull('deleted_at');
        })->where('status', '=', 'open')->count();
        $data['totalMessages'] = Chat::count() + $staticNumberMessages;
        $data['totalAPI'] = $staticNumberAPI;
        $data['totalSubjects'] = $staticNumberSubjects;
        $data['totalStates'] = $staticNumberStates;
        $data['totalClasses'] = $staticNumberClasses;

        $data['usersByClass'] = Students::select(DB::raw('class, count(*) as count'))
            ->groupBy('class')
            ->get();
        $data['usersByGender'] = Students::select(DB::raw('gender, count(*) as count'))
            ->groupBy('gender')
            ->get();
        $data['usersByState'] = Students::select(DB::raw('state, count(*) as count'))
            ->groupBy('state')
            ->get();
        $data['usersByAge'] = Students::select(DB::raw('YEAR(CURDATE()) - birth_year as age, count(*) as count'))
            ->groupBy('age')
            ->get();

        $activityData = ActivityLog::select(DB::raw('DAYNAME(action_date) as day, HOUR(action_date) as hour, COUNT(*) as count'))
            ->where('action_date', '>=', Carbon::now()->subDays($duration))
            ->groupBy('day', 'hour')
            ->get()
            ->groupBy('day')
            ->map(function ($day) {
                return $day->pluck('count', 'hour');
            });

        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $activityHeatMap = [];

        foreach ($daysOfWeek as $day) {
            $hours = [];
            for ($i = 0; $i < 24; $i++) {
                $hours[$i] = isset($activityData[$day]) ? $activityData[$day]->get($i, 0) : 0; 
            }
            $activityHeatMap[$day] = $hours;
        }

        $data['activityHeatMap'] = $activityHeatMap;

        $data['payments'] = BillingResource::collection($billingRows);
        $data['period'] = $this->period();
        $data['newUsers'] = $this->newUsers();
        $data['newStudents'] = $this->newStudents();
        $data['revenue'] = $this->revenue();
        $data['title'] = __('Dashboard');

        return Inertia::render('Admin/Dashboard', $data);
    }

    private function period()
    {
        $currentDate = Carbon::now();
        $dateArray = [];

        for ($i = 0; $i < 7; $i++) {
            $currentDate->startOfDay();
            $dateArray[] = $currentDate->format('Y-m-d\TH:i:s.000\Z');
            $currentDate->subDay();
        }

        $dateArray = array_reverse($dateArray);

        return $dateArray;
    }

    private function newUsers()
    {
        $userCounts = [];

        foreach ($this->period() as $dateString) {
            $date = Carbon::parse($dateString);
            $userCount = User::whereDate('created_at', $date->toDateString())->count();
            $userCounts[] = $userCount;
        }

        return $userCounts;
    }

    private function newStudents()
    {
        $studentCounts = [];

        foreach ($this->period() as $dateString) {
            $date = Carbon::parse($dateString);
            $studentCount = Students::whereDate('created_at', $date->toDateString())->count();
            $studentCounts[] = $studentCount;
        }

        return $studentCounts;
    }

    private function revenue()
    {
        $billingCounts = [];

        foreach ($this->period() as $dateString) {
            $date = Carbon::parse($dateString);
            $billingCount = BillingTransaction::whereHas('organization', function ($query) {
                $query->whereNull('deleted_at');
            })->where('entity_type', '=', 'payment')
                ->whereDate('updated_at', $date->toDateString())
                ->count();

            $billingCounts[] = $billingCount;
        }

        return $billingCounts;
    }
}
