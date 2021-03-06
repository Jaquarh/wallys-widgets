<?php

/**
 * @copyright (c) Kyle Jeynes <kylejeynes97@icloud.com>
 * @author Kyle Jeynes
 * 
 * This requires PHP ^8 - To run PHPUnit tests on < 7.4, remove the string|null array|bool datatype notations in the functions.
 */
class WallysWidgetsCalculator {

    /**
     * Holds the pack sizes the company sells.
     * @var array
     */
    private array $packSizes = [];

    /**
     * Holds the customers request of size.
     * @var int
     */
    private int $widgetsRequired;

    /**
     * Holds the assigned packs that best fit to the packSizes.
     * @var array
     */
    private array $packsAssigned = [];

    /**
     * Multidimensional array holding all the possibiltiies.
     * @var array
     */
    private array $potentialPackSizes = [];

    /**
     * Turns on and off debug mode
     * @var bool
     */
    private bool $debug = false;

    /**
     * Since we shift arrays, we need a physical copy
     * @var int
     */
    private array $defaultPackSizes = [];

    /**
     * If 5000 are required, and there's a pack for 250 then it assumes 250 is a perfect fit, this helps avoid that
     * @var bool
     */
    private bool $foundExactDivision = false;

    /**
     * Check what iteration we are currently at
     * @var int
     */
    private int $iterations = 1;

    /**
     * Gets the packs based on the widgets required and packs being sold.
     * @param int $widgetsRequired
     * @param array $packSizes
     * @return array
     */
    public function getPacks(int $widgetsRequired, array $packSizes): array {
        $this->widgetsRequired = $widgetsRequired;
        $this->packSizes = $packSizes;

        # Since we iterate over this method, we do not want to reassign any new $packSizes that have been shifted
        $this->defaultPackSizes = count($this->defaultPackSizes) === 0 ? $packSizes : $this->defaultPackSizes;

        if ($this->hasExactMatch()) {
            $this->assignPack(1, $widgetsRequired);
            return $this->getPacksAssigned();
        }

        /**
         * If there is an exact division, we still need
         * to compare against other possible solutions.
         */
        if (($packSize = $this->hasExactDivision())) {
            $this->assignAnyDividable();
            $this->foundExactDivision = true;
        } else {
            $this->assignAnyByOrder();

            $this->packSizes = $packSizes;
            $this->assignRemaining();
        }

        $this->potentialPackSizes[] = $this->packsAssigned;
        $this->packsAssigned = [];

        arsort($packSizes);

        if ($this->foundExactDivision) {
            if ($this->iterations++ === 1) {
                $this->getPacks($widgetsRequired, $packSizes);
                $this->widgetsRequired = $widgetsRequired;
                return $this->compareArrayValuesSum();
            }
        }

        if (($highestPackSize = array_shift($packSizes)) !== null && count($packSizes) !== 0) {
            return $this->getPacks($widgetsRequired, $packSizes);
        }

        $this->widgetsRequired = $widgetsRequired;

        if(count(($solution = $this->TestForBetterMatch($this->compareArrayValuesSum()))) > 0)
                return $this->failSafeNeg($solution);
        
        return [end($this->defaultPackSizes) => 1];
    }

    /**
     * Checks for an exact match
     * @return bool
     */
    protected function hasExactMatch(): bool {
        return in_array($this->widgetsRequired, $this->packSizes);
    }

    /**
     * Assigns the $packSize and $quantity of it to the builder
     * @param int $quantity
     * @param int $packSize
     * @return void
     */
    protected function assignPack(int $quantity, int $packSize): void {
        $this->log('assignPack', 'BEFORE | WITH: ' . $packSize . ' * ' . $quantity);

        if ($this->widgetsRequired === 0)
            return;

        $this->deductWidgets($packSize * $quantity);

        $this->log('assignPack', 'AFTER');

        if (in_array($packSize, array_keys($this->packsAssigned))) {
            $this->packsAssigned[$packSize] += $quantity;
            return;
        }

        $this->packsAssigned[$packSize] = $quantity;
    }

    /**
     * Deducts the widgetsRequired
     * @param int $widgets
     * @return void
     */
    protected function deductWidgets(int $widgets): void {
        $this->widgetsRequired -= $widgets;
    }

    /**
     * Return the builder
     * @return array
     */
    protected function getPacksAssigned(): array {
        return $this->packsAssigned;
    }

    /**
     * If there is an exact division in the widgetsRemaining and any packSizes return it
     * Otherwise return false.
     * @return array|bool
     */
    protected function hasExactDivision(): array|bool {
        $this->log('hasExactDivision', null);

        if ($this->foundExactDivision)
            return false;

        return array_filter($this->packSizes, function($packSize) {
                    return $this->widgetsRequired % $packSize === 0;
                }) ?? false;
    }

    /**
     * If any are dividable, calculate how many to assign and assign them
     * @return WallysWidgetsCalculator
     */
    protected function assignAnyDividable(): WallysWidgetsCalculator {
        $this->log('assignAnyDividable', null);

        while (($packSizes = $this->hasExactDivision())) {
            if ($this->widgetsRequired === 0)
                break;

            arsort($packSizes);
            $this->assignPack(intval($this->widgetsRequired / ($size = array_shift($packSizes)), 0), $size);
        }

        return $this;
    }

    /**
     * This is where the magic happens
     * @return void
     */
    protected function assignAnyByOrder(): void {
        $this->log('assignAnyByOrder', null);

        # Until we either hit 0 or run out of packSizes to test
        while ($this->widgetsRequired >= 0) {
            $this->filterPackSizes();

            $packSize = array_shift($this->packSizes);

            # We break here because we now need to figure out the best solution of bringing it negative
            if ($packSize === null)
                break;

            if ($this->widgetsRequired - $packSize >= 0) {
                $this->assignPack(1, $packSize);
                array_unshift($this->packSizes, $packSize);
            }

            if ($this->widgetsRequired >= 0)
                $this->assignAnyDividable();
        }
    }

    /**
     * This filters what packSizes will fit and sorts them
     * @return WallysWidgetsCalculator#
     */
    protected function filterPackSizes(): WallysWidgetsCalculator {
        $this->log('filterPackSizes', null);

        arsort($this->packSizes);

        $this->packSizes = array_filter($this->packSizes, function($packSize) {
            return $this->widgetsRequired >= $packSize;
        });

        return $this;
    }
    
    /**
     * Removes a packSize or reduces its quantity
     * @param int $packSize
     * @return void
     */
    protected function unassignPack(int $quantity, int $packSize): void
    {
        if(in_array($packSize, array_keys($this->packsAssigned)))
        {
            if($this->packsAssigned[$packSize] - $quantity <= 0)
            {
                unset($this->packsAssigned[$packSize]);
                return;
            }
            
            $this->packsAssigned[$packSize] = $this->packsAssigned[$packSize] - $quantity;
        }
    }

    /**
     * There is no more packSizes that will bring it to zero
     * We loop through each and see which is the best packSize to use to bring it over
     * @return void
     */
    protected function assignRemaining(): void {
        $this->log('assignRemaining', null);
        arsort($this->packSizes);

        $widgetDeduction = 0;
        $packSizeToUse = end($this->packSizes);
        $quantity = 1;

        foreach ($this->packSizes as $packSize) {
            if (($widgetsRequired = $this->widgetsRequired - $packSize) > $widgetDeduction) {
                $widgetDeduction = $widgetDeduction = $widgetsRequired;
                $packSizeToUse = $packSize;
            } else {
                foreach (range(2, 10) as $x) {
                    if (($widgetsRequired = ($this->widgetsRequired - $packSize) * $x) > $widgetDeduction) {
                        $widgetDeduction = $widgetDeduction = $widgetsRequired;
                        $packSizeToUse = $packSize;
                        $quantity = $x;
                    }
                }
            }
        }

        $this->assignPack($quantity, $packSizeToUse);
    }

    /**
     * Compare all the possible solutions based on widget total or amount of quantity.
     * @return array
     */
    protected function compareArrayValuesSum(): array {
        $sum = $this->widgetsRequired;
        $packToUse = [];

        krsort($this->potentialPackSizes);

        foreach ($this->potentialPackSizes as $packsAssigned) {
            if (($newSum = array_sum(array_values($packsAssigned))) < $sum) {
                $sum = $newSum;
                $packToUse = $packsAssigned;
            }
        }

        rsort($this->defaultPackSizes);

        $currentPack = 0;

        foreach ($packToUse as $packSize => $quantity)
            $currentPack += $packSize * $quantity;

        foreach ($this->defaultPackSizes as $highestPack) {
            if ($currentPack === $this->widgetsRequired)
                return $packToUse;

            if ($this->widgetsRequired - $highestPack > 0)
                return $packToUse;

            $packToUse = $currentPack < $highestPack ? $packToUse : [$highestPack => 1];
        }
        
        arsort($this->defaultPackSizes);

        return $packToUse;
    }
    
    /**
     * Test for a better solution by removing the last mass assignment for one assignment
     * @param array $solution
     * @param array $packSizes
     * @return array
     */
    protected function TestForBetterMatch(array $solution): array
    {
        $keys = array_keys($solution);
        $packSizeToRemove = end($keys);
        
        # We know that the intented better solution could replace the end() one
        $packSizeToTestAgainst = $packSizeToRemove * intval(end($solution));
        
        foreach($this->defaultPackSizes as $packSize)
        {
            if($packSize === $packSizeToTestAgainst)
            {
                unset($solution[$packSizeToRemove]);
                $solution[$packSize] = 1;
            }
        }
        
        return $solution;
    }
    
    /**
     * If no solution was found to bring it negative or to 0
     * Fail safe with the highest pack size
     * @param array $solution
     * @return array
     */
    public function failSafeNeg(array $solution): array
    {
        $total = 0;
        
        foreach($solution as $packSize => $quantity)
            $total += $packSize * $quantity;
        
        if($total < $this->widgetsRequired)
        {
            return [($packSize = array_shift($this->defaultPackSizes)) => ceil($this->widgetsRequired / $packSize)];
        }
        
        return $solution;
    }

    /**
     * Debug for me running on PHP ^8
     * @param string $title
     * @param string|null $message
     * @return WallysWidgetsCalculator
     */
    private function log(string $title, string|null $message): WallysWidgetsCalculator {
        if (!$this->debug) {
            return $this;
        }

        echo "[{$title}] : {$message} <br />";
        echo "packSizes: " . implode(', ', array_values($this->packSizes)) . "<br />";
        echo "widgetsRequired: {$this->widgetsRequired}<br /> <br />";
        return $this;
    }

}