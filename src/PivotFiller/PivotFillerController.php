<?php

namespace Larangular\PivotSupport\PivotFiller;

class PivotFillerController {

    private $routePrefix = 'sura.vehicles.refactor.';
    private $companyAssets;
    private $makerPivot;
    private $localModel;
    private $foreignModel;
    private $pivotModel;

    public function __construct() {
        $this->companyAssets = $this->getCompanyAssets();
        $this->localModel = new $this->localModel();

    }

    public function index() {
        return view('pivot-filler::index', [
            'companyAssets' => $this->companyAssets
        ]);
    }

    public function show($id) {
        $asset = $this->getCompanyAsset($id);
        $sourceMakers = $this->searchMaker($asset->marca);

        return view('sura.vehicles.refactor.index', [
            'companyAssets' => $this->companyAssets,
            'companyAsset'  => $asset->toArray(),
        ]);
    }

    private function getCompanyAssets() {
        return $this->foreignModel()->all();
    }


    private function localModelInstance() {
        if(is_null($this->localModel)) {
            $name = $this->localModel();
            $this->localModel = new $name;
        }

        return $this->localModel;
    }

    private function foreignModelInstance() {
        if(is_null($this->foreignModel)) {
            $name = $this->foreignModel();
            $this->foreignModel = new $name;
        }

        return $this->foreignModel;
    }

    private function pivotModelInstance() {
        if(is_null($this->pivotModel)) {
            $name = $this->pivotModel();
            $this->pivotModel = new $name;
        }

        return $this->pivotModel;
    }


}
