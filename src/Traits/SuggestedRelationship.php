<?php

namespace Larangular\PivotSupport\Traits;

use Larangular\PivotSupport\Contracts\HasForeignDescription;
use Larangular\PivotSupport\Contracts\HasLocalDescription;
use Larangular\PivotSupport\Contracts\HasPivotDescription;
use Larangular\PivotSupport\Model\RelationshipDescription;
use Larangular\Support\Facades\Instance;

trait SuggestedRelationship {

    abstract public function localModel(): string;

    public function getSuggestedRelationshipAttribute() {
        if (!Instance::instanceOf($this, \Illuminate\Database\Eloquent\Model::class) || !Instance::hasTrait($this,
                ParentPivotModel::class) || is_null($this->pivot)) {
            return null;
        }

        $localDescription = $this->pivotModel_getLocalDescription();

        $term = '%' . $this->{$localDescription->localKey} . '%';
        $related = $localDescription->related;
        $model = new $related;

        return $model->where($localDescription->foreignKey, 'like', $term)->first();
    }

    private function pivotModel_getLocalDescription(): RelationshipDescription {
        $result = new RelationshipDescription($this->localModel(), 'name', 'name');
        if (Instance::hasInterface($this, HasLocalDescription::class)) {
            $result = $this->pivotModel_getLocalDescription();
        }

        return $result;
    }

}
