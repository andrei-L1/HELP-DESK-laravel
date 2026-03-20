<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\SlaPolicy;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SlaService
{
    /**
     * Get business hours configuration from settings database
     */
    protected function getConfiguration()
    {
        return [
            'start' => Setting::get('business_hours_start', '09:00'),
            'end'   => Setting::get('business_hours_end', '18:00'),
            'days'  => Setting::get('business_days', [1, 2, 3, 4, 5], 'json'),
        ];
    }

    /**
     * Calculate the "due_at" timestamp for a ticket based on SLA and business hours.
     */
    public function calculateDeadline(Carbon $startTime, ?SlaPolicy $policy): ?Carbon
    {
        if (!$policy || !$policy->resolution_time) {
            return null;
        }

        $minutesToAdd = (int) $policy->resolution_time;

        if (!$policy->business_hours_only) {
            return $startTime->copy()->addMinutes($minutesToAdd);
        }

        $config = $this->getConfiguration();
        $startHour = (int) explode(':', $config['start'])[0];
        $startMin  = (int) explode(':', $config['start'])[1];
        $endHour   = (int) explode(':', $config['end'])[0];
        $endMin    = (int) explode(':', $config['end'])[1];
        $activeDays = $config['days'];

        $currentTime = $startTime->copy();
        $limit = 1000; 

        while ($minutesToAdd > 0 && $limit > 0) {
            $limit--;

            // 1. Check if today is a business day
            if (!in_array($currentTime->dayOfWeekIso, $activeDays)) {
                $currentTime->addDay()->startOfDay()->addHours($startHour)->addMinutes($startMin);
                continue;
            }

            // 2. Move to next business day if we are past business hours
            $closeTime = $currentTime->copy()->startOfDay()->addHours($endHour)->addMinutes($endMin);
            if ($currentTime->greaterThanOrEqualTo($closeTime)) {
                $currentTime->addDay()->startOfDay()->addHours($startHour)->addMinutes($startMin);
                continue;
            }

            // 3. Move to start of business hours if we are before business hours
            $openTime = $currentTime->copy()->startOfDay()->addHours($startHour)->addMinutes($startMin);
            if ($currentTime->lessThan($openTime)) {
                $currentTime->startOfDay()->addHours($startHour)->addMinutes($startMin);
                continue;
            }

            // 4. Calculate how many minutes are left in the current business day
            $minutesToBusinessClose = $currentTime->diffInMinutes($closeTime);

            if ($minutesToAdd <= $minutesToBusinessClose) {
                $currentTime->addMinutes($minutesToAdd);
                $minutesToAdd = 0;
            } else {
                $minutesToAdd -= $minutesToBusinessClose;
                $currentTime->addDay()->startOfDay()->addHours($startHour)->addMinutes($startMin);
            }
        }

        return $currentTime;
    }
}
