<?php

/**
 * @copyright (c) Kyle Jeynes <kylejeynes97@icloud.com>
 * @author Kyle Jeynes
 */

class WallysWidgetsCalculator
{
    /**
     * 
     * @var array Holds the pack sizes the company sells.
     */
    protected array $packSizes = [];
    
    /**
     * 
     * @var int Holds the customers request of size.
     */
    protected int $widgetsRequired;
    
    /**
     * 
     * @var array Holds the assigned packs that best fit to the packSizes.
     */
    protected array $packsAssigned = [];

    /**
     * Gets the packs based on the widgets required and packs being sold.
     * @param int $widgetsRequired
     * @param array $packSizes
     * @return array
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
            $highPackSize = array_shift($this->packSizes);
            
            # If null, we'll assume no more packs can be used, so we'll fail safe with the lowest pack that will bring it negative
            if($highPackSize === null):
                # Resort the packs replacing keys
                rsort($packSizes);
            
                # Find the pack that'll bring it negative and break
                while(true):
                    if($this->widgetsRequired - end($packSizes) < 0):
                        $this->assignPack(end($packSizes));
                        break;
                    endif;
                    array_pop($packSizes);
                endwhile;
                
                # This will return the packsAssigned
                break;
            endif;
            
            # Calculate if we can use this pack
            if(count($this->packSizes) !== 1 && $this->widgetsRequired - $highPackSize >= 0):
                # We can use this
                $this->assignPack($highPackSize);
                # Reshift this pack to the array
                array_unshift($this->packSizes, $highPackSize);
            else:
                # Check how many times this pack can fit into the remaining and assign
                $this->assignAnyRemaining($highPackSize);
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
            return $this->widgetsRequired >= $pack;
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
        
        $this->packsAssigned[$packSize] = 1;
    }
    
    public function assignAnyRemaining(int $pack): void
    {
        for($i = 1; $i <= intval(floor($this->widgetsRequired / $pack), 0); $i++):
            $this->assignPack($pack);
        endfor;
    }
}