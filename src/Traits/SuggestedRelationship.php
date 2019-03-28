<?php

namespace Larangular\PivotSupport\Traits;

use Larangular\PivotSupport\Contracts\HasForeignDescription;
use Larangular\PivotSupport\Contracts\HasLocalDescription;
use Larangular\PivotSupport\Contracts\HasPivotDescription;
use Larangular\PivotSupport\Contracts\HasSuggestedRelationshipFilter;
use Larangular\PivotSupport\Model\RelationshipDescription;
use Larangular\Support\Facades\Instance;

trait SuggestedRelationship {

    abstract public function localModel(): string;

    public function getSuggestedRelationshipAttribute() {
        if (!Instance::instanceOf($this, \Illuminate\Database\Eloquent\Model::class) || !Instance::hasTrait($this,
                ParentPivotModel::class) || !is_null($this->pivot)) {
            return null;
        }

        $localDescription = $this->pivotModel_getLocalDescription();

        $term = '%' . $this->{$localDescription->localKey} . '%';
        $related = $localDescription->related;
        $model = new $related;

        $filter = $this->pivotModel_getSuggestedRelationshipFilter();
        array_push($filter, [$localDescription->foreignKey, 'like', $term]);

        return $model->where($filter)
                     ->first();
    }

    private function pivotModel_getSuggestedRelationshipFilter(): array {
        if (Instance::hasInterface($this, HasSuggestedRelationshipFilter::class)) {
            return $this->suggestedRelationshipFilter();
        }
        return [];
    }

    private function pivotModel_getLocalDescription(): RelationshipDescription {
        $result = new RelationshipDescription($this->localModel(), 'name', 'name');
        if (Instance::hasInterface($this, HasLocalDescription::class)) {
            $result = $this->pivotModel_getLocalDescription();
        }

        return $result;
    }

}
