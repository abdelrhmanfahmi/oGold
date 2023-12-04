<?php

namespace App\Services;

class TotalVolumesService {

    public function getTotalVolumes($data)
    {
        try{
            $totalVolumes = 0;
            if(count($data->positions) > 0){
                foreach($data->positions as $d){
                    $totalVolumes += $d->volume;
                }
            }

            return $totalVolumes;

        }catch(\Exception $e){
            return $e;
        }
    }

}
