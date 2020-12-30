<?php

class WallysWidgetsCalculator
{
    protected array $packSizes = [];
    protected integer $widgetsRequired;
    protected array $packsAssigned = [];
    
    /**
     * Your solution should return an array with the pack sizes as the key
     * and the number of packs in that size as the value.
     *
     * Pack sizes that are not required should not be included.
     *
     * Example:
     *
     * getPacks(251, [
     *  250,
     *  500,
     *  1000
     * ])
     *
     * should return:
     *
     * [500 => 1]
     */
    public function getPacks(int $widgetsRequired, array $packSizes): array
    {
        # Store the values in the object scope
        $this->packSizes = array_values($packSizes);
        # Store the required widgets into the object scope
        $this->widgetsRequired = $widgetsRequired;
        
        # Check for an exact match and return if there is
        if($this->checkExactMatch())
            return [$this->widgetsRequired => 1];
        
        # Update the widgetsRequired each time we assign a pack
        while($this->widgetsRequired !== 0):
            # Write to the packsizes with ones we can only work with
            $this->filterPackSizes();
            # Pop the first element which will be the highest pack size
            $highePackSize = array_shift($this->packSizes);
            # Calculate if we can use this pack
            if(count($this->packSizes) !== 1 && $this->widgetsRequired - $highePackSize >= 0):
                # We can use this
                $this->assignPack($highePackSize);
                # Reshift this pack to the array
                array_unshift($this->packSizes, $highePackSize);
            else:
                # We must check how many times this pack can fit into the remaining
                $packQuantity = intval(floor($this->packSizes / $highePackSize), 0);
                for($i = 1; $i >= $packQuantity; $i++):
                    $this->assignPack($highePackSize);
                endfor;
            endif;
        endwhile;
        
        return $this->packsAssigned;
    }
    
    /**
     * Checks for exact pack size match
     * @return bool
     */
    private function checkExactMatch(): bool
    {
        return in_array($this->widgetsRequired, $this->packSizes);
    }
    
    /**
     * Filters the packs for packs that are suitable for use and sorts them high to low.
     * @return void
     */
    private function filterPackSizes(): void
    {
        $this->packSizes = array_filter($this->packSizes, function($pack) {
            return $this->widgetsRequired <= $pack;
        });
        
        # Sort the new array descending and work from high to low
        arsort($this->packSizes);
    }
    
    /**
     * Assigns a pack to the packsAssigned array or increments the value.
     * @param int $packSize
     * @return type
     */
    private function assignPack(int $packSize): void
    {
        # Deduct the packsize from the widgetsRequired
        $this->widgetsRequired = $this->widgetsRequired - $packSize;
        
        # isset() could be used here, also
        if(in_array($packSize, array_keys($this->packsAssigned)))
        {
            # Increase the qauntity of the pack
            $this->packsAssigned[$packSize]++;
            return;
        }
        
        $this->packSizes[$packsize] = 1;
    }
}
