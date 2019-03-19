<?php

namespace Larangular\PivotSupport\Contracts;

use Larangular\PivotSupport\Model\RelationshipDescription;

interface HasPivotDescription {

    public function pivotDescription(): RelationshipDescription;

}
