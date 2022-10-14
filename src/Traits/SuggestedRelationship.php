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


        $filter = $this->pivotModel_getSuggestedRelationshipFilter();
        $descriptionFilter = $this->pivotModel_getFilter($localDescription);
        return $model->where($filter)->whereRaw($descriptionFilter['query'], $descriptionFilter['bindings'])->first();
    }

    private function pivotModel_getFilter(RelationshipDescription $description, bool $strict = true) {
        $filter = $this->pivotModel_getSuggestedRelationshipFilter();
        $term = $this->{$description->localKey};
        if(!$strict) {
            $term = '%'.$term.'%';
        }

        $replaceableTerms = [" ", "-", "."];
        $replaceFn = function ($entry, $replaceTerms) {
            $r = '?';
            foreach($replaceTerms as $term) {
                $r = 'REPLACE('.$r.', "'.$term.'", "")';
            }
            return $r;
        };

        return [
            'query' => DB::raw($replaceFn($description->foreignKey, $replaceableTerms).' like '.$replaceFn($term, $replaceableTerms)),
            'bindings' => [$description->foreignKey, '"'.$term.'"']
        ];

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
