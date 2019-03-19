<?php

namespace Larangular\PivotSupport\Traits;

use Larangular\PivotSupport\Contracts\HasForeignDescription;
use Larangular\PivotSupport\Contracts\HasLocalDescription;
use Larangular\PivotSupport\Contracts\HasPivotDescription;
use Larangular\PivotSupport\Model\RelationshipDescription;
use Larangular\Support\Facades\Instance;

trait PivotModel {

    abstract public function localModel(): string;

    public function foreign() {
        if(!Instance::instanceOf($this, \Illuminate\Database\Eloquent\Model::class)) {
            return null;
        }
        $foreignDescription = $this->pivotModel_getForeignDescription();
        return $this->hasOne($foreignDescription->related, $foreignDescription->foreignKey, $foreignDescription->localKey);
    }

    public function local() {
        if(!Instance::instanceOf($this, \Illuminate\Database\Eloquent\Model::class)) {
            return null;
        }
        $pivotDescription = $this->pivotModel_getLocalDescription();
        return $this->hasOne($pivotDescription->related, $pivotDescription->foreignKey, $pivotDescription->localKey);
    }

    private function pivotModel_getLocalDescription(): RelationshipDescription {
        $result = new RelationshipDescription($this->localModel(), 'id', 'local_value');
        if(Instance::hasInterface($this, HasLocalDescription::class)) {
            $result = $this->pivotModel_getLocalDescription();
        }

        return $result;
    }

    private function pivotModel_getForeignDescription(): RelationshipDescription {
        $result = new RelationshipDescription(str_replace('Pivot', '', __CLASS__), 'id', 'foreign_value');
        if(Instance::hasInterface($this, HasForeignDescription::class)) {
            $result = $this->getForeignDescription();
        }

        return $result;
    }

}
