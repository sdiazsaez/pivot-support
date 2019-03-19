<?php

namespace Larangular\PivotSupport\Contracts;

use Larangular\PivotSupport\Model\RelationshipDescription;

interface HasLocalDescription {

    public function localDescription(): RelationshipDescription;

}
