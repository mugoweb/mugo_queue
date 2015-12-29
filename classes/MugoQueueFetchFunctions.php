<?php

class MugoQueueFetchFunctions
{

    public static function getPaginationSteps( $currentIndex, $totalPages )
    {
        $return = array();

        $leftDistance = $currentIndex - 2;
        $rightDistance = $totalPages - $currentIndex - 1;

        $leftSteps = min( 4, $leftDistance );
        $rightSteps = min( 4, $rightDistance );

        for( $x=$leftSteps; $x >= 0; $x-- )
        {
            $nextIndex = ceil( pow( ($x/4), 3 ) * $leftDistance  ) + 1;

            // defined boundries
            if( $x < $leftSteps - 1 )
            {
                $nextIndex = min( $nextIndex, pow( 5, $x ) );
            }

            $nextIndex = max( $nextIndex, $x + 1 );

            $return[] = $currentIndex - $nextIndex;
        }

        $return[] = $currentIndex;

        for( $x=0; $x <= $rightSteps; $x++ )
        {
            $nextIndex = ceil( pow( ($x/4), 3 ) * $rightDistance ) + 1;

            // defined boundries
            if( $x < $rightSteps - 2 )
            {
                $nextIndex = min( $nextIndex, pow( 5, $x ) );
            }

            $nextIndex = max( $nextIndex, $x + 1 );

            $return[] = $currentIndex + $nextIndex;
        }

        return array( 'result' => $return );
    }
}
