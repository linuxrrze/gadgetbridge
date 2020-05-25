<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 * @copyright Copyright (c) 2020 Dan Meltzer <dmeltzer.devel@gmail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,e
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.e
 *
 */
namespace OCA\GadgetBridge\Activity;

use Carbon\Carbon;


// This is essentially a 1:1 copy from Gadgetbridge, reimplemented in php.

class ActivityAmount {
    public $activityKind;
    public $percent;
    public $totalSeconds;
    public $totalSteps;
    public $startDate;
    public $endDate;

    public function __construct($activityKind)
    {
        $this->activityKind = $activityKind;
    }

    public function addSeconds($seconds)
    {
        $this->totalSeconds += $seconds;
    }

    public function addSteps($steps)
    {
        $this->totalSteps += $steps;
    }

    public function totalSeconds()
    {
        return $this->totalSeconds;
    }
    
    public function totalSteps()
    {
        return $this->totalSteps;
    }

    public function activityKind()
    {
        return $this->activityKind;
    }

    public function percent()
    {
        return $this->percent;
    }

    public function setPercent($percent)
    {
        $this->percent = $percent;
    }

    public function name()
    {
        switch($this->activityKind) {
            case ActivityKind::TYPE_DEEP_SLEEP:
                return "Deep Sleep";
            case ActivityKind::TYPE_LIGHT_SLEEP:
                return "Light Sleep";
        }
        return "Activity"; // fall-through;
    }

    public function startDate()
    {
        return $this->startDate;
    }

    public function setStartDate($milliseconds) {
        if(is_null($this->startDate)) {
            $this->startDate = Carbon::createFromTimestampMs($milliseconds);
        }
    }

    public function endDate() {
        return $this->endDate;
    }

    public function setEndDate($milliseconds) {
        $this->endDate = Carbon::createFromTimestampMs($milliseconds);
    }
}
