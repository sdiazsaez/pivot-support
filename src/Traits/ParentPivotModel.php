<?php

namespace Larangular\PivotSupport\Traits;

use Larangular\PivotSupport\Contracts\HasPivotDescription;
use Larangular\PivotSupport\Pivot\PivotDescription;
use Larangular\Support\Facades\Instance;

trait ParentPivotModel {

    public function pivot() {
        if(!Instance::instanceOf($this, \Illuminate\Database\Eloquent\Model::class)) {
            return null;
        }

        $pivotDescription = $this->hasPivot_getPivotDescription();


        return $this->hasOne($pivotDescription->related, $pivotDescription->foreignKey, $pivotDescription->localKey);
    }

    private function hasPivot_getPivotDescription(): PivotDescription {
        $result = new PivotDescription($this->table.'_pìvot', 'foreign_value', 'id');
        if(Instance::hasInterface($this, HasPivotDescription::class)) {
            $result = $this->getPivotDescription();
        }

        return $result;
    }

}
