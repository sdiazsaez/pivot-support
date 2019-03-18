<?php

namespace Larangular\PivotSupport\Pivot;

class PivotDescription {

    public $related;
    public $foreignKey;
    public $localKey;

    public function __construct(string $related, string $foreignKey, string $localKey) {
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

}
