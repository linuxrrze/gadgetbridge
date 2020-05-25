<?php
namespace OCA\GadgetBridge\Activity;

class ActivityAmounts
{
    public $amounts = [];
    public $totalSeconds = 0;

    public function addAmount(ActivityAmount $amount): void
    {
        if($amount->totalSeconds() <= 0) return;
        $this->amounts[] = $amount;
        $this->totalSeconds += $amount->totalSeconds();
    }

    public function getAmounts(): Array
    {
        return $this->amounts;
    }

    public function getTotalSeconds(): int
    {
        return $this->totalSeconds;
    }

    public function calculatePercentages()
    {
        foreach( $amounts as &$amount) {
            $fraction = $amount->totalSeconds / (float) $this->totalSeconds;
            $amount->setPercent((float)$fraction * 100);
        }
        unset($amount);
    }
}
