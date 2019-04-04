<?php

namespace Larangular\PivotSupport\Traits;

use Larangular\PivotSupport\Contracts\HasPivotDescription;
use Larangular\PivotSupport\Model\RelationshipDescription;
use Larangular\Support\Facades\Instance;

trait ParentPivotModel {

    public function pivot() {
        if(!Instance::instanceOf($this, \Illuminate\Database\Eloquent\Model::class)) {
            return null;
        }
        $pivotDescription = $this->parentPivotModel_getPivotDescription();
        return $this->hasOne($pivotDescription->related, $pivotDescription->foreignKey, $pivotDescription->localKey);
    }

    public function scopeByLocal($query, $localId) {
        return $query->whereHas('pivot', function($query) use($localId) {
            $query->where('local_value', $localId);
        });
    }

    private function parentPivotModel_getPivotDescription(): RelationshipDescription {
        $result = new RelationshipDescription(__CLASS__.'Pivot', 'foreign_value', 'id');
        if(Instance::hasInterface($this, HasPivotDescription::class)) {
            $result = $this->getPivotDescription();
        }

        return $result;
    }

}
