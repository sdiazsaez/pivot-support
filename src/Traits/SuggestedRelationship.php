<?php

namespace Larangular\PivotSupport\Traits;

use Larangular\PivotSupport\Contracts\HasForeignDescription;
use Larangular\PivotSupport\Contracts\HasLocalDescription;
use Larangular\PivotSupport\Contracts\HasPivotDescription;
use Larangular\PivotSupport\Contracts\HasSuggestedRelationshipFilter;
use Larangular\PivotSupport\Model\RelationshipDescription;
use Larangular\Support\Facades\Instance;
use Illuminate\Support\Facades\DB;

trait SuggestedRelationship {

    abstract public function localModel(): string;

    public function getSuggestedRelationshipAttribute() {
        if (!Instance::instanceOf($this, \Illuminate\Database\Eloquent\Model::class) || !Instance::hasTrait($this,
                ParentPivotModel::class) || !is_null($this->pivot)) {
            return null;
        }

        $localDescription = $this->pivotModel_getLocalDescription();
        $related = $localDescription->related;
        $model = new $related;

        return $model->where($this->pivotModel_getFilter($localDescription))->first();

        /*
        if(is_null($response)) {
            $response = $model->where($this->pivotModel_getFilter($localDescription, false))->first();
        }

        return $response;
        */
    }

    private function pivotModel_getFilter(RelationshipDescription $description, bool $strict = true): array {
        $filter = $this->pivotModel_getSuggestedRelationshipFilter();
        $term = $this->{$description->localKey};
        if(!$strict) {
            $term = '%'.$term.'%';
        }


        array_push($filter, [
            DB::raw('REPLACE(REPLACE(REPLACE('.$description->foreignKey.', " ", ""), "-", ""), ".", "")'),
            'like',
            DB::raw('REPLACE(REPLACE(REPLACE("'.$term.'", " ", ""), "-", ""), ".", "")'),
        ]);

        return $filter;
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
