<?php

namespace Larangular\PivotSupport\Contracts;

use Larangular\PivotSupport\Pivot\PivotDescription;

interface HasPivotDescription {

    public function pivotDescription(): PivotDescription;

}
