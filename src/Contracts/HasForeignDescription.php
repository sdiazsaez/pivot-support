<?php

namespace Larangular\PivotSupport\Contracts;

use Larangular\PivotSupport\Model\RelationshipDescription;

interface HasForeignDescription {

    public function foreignDescription(): RelationshipDescription;

}
