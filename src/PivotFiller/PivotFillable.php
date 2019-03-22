<?php

namespace Larangular\PivotSupport\PivotFiller;

use Illuminate\Http\Request;

abstract class PivotFillable {

    public  $foreignAssets;
    public  $localAssets;
    private $localModel;
    private $foreignModel;
    private $pivotModel;
    private $routePrefix;

    public abstract function routePrefix();

    public abstract function localModel();

    public abstract function foreignModel();

    public abstract function pivotModel();


    public function __construct() {
        $this->foreignAssets = $this->getCompanyAssets();
        $this->localAssets = $this->getLocalAssets();
        $this->routePrefix = $this->routePrefix();
    }

    public function index() {
        return view('pivot-filler::index', [
            'foreignAssets' => $this->foreignAssets,
            'localAssets'   => $this->localAssets,
            'form'          => $this->getFormOptions(),
        ]);
    }

    public function show($id) {
        $asset = $this->getCompanyAsset($id);
        $sourceMakers = $this->searchMaker($asset->marca);

        return view('sura.vehicles.refactor.index', [
            'companyAssets' => $this->foreignAssets,
            'companyAsset'  => $asset->toArray(),
        ]);
    }

    public function store(Request $request) {
        if($request->has('foreign_value') && $request->has('local_value')) {
            $item = $this->pivotModelInstance()->create([
                'foreign_value' => $request->input('foreign_value'),
                'local_value' => $request->input('local_value')
            ]);
            $item->save();
        }
        return redirect(route($this->routePrefix . '.pivot-filler.index'));
    }

    private function getCompanyAssets() {
        return $this->foreignModelInstance()
                    ->all();
    }

    private function getLocalAssets() {
        return $this->localModelInstance()
                    ->all();
    }

    private function getFormOptions() {
        return [
            'action' => route($this->routePrefix . '.pivot-filler.store'),
        ];
    }


    private function localModelInstance() {
        if (is_null($this->localModel)) {
            $name = $this->localModel();
            $this->localModel = new $name;
        }

        return $this->localModel;
    }

    private function foreignModelInstance() {
        if (is_null($this->foreignModel)) {
            $name = $this->foreignModel();
            $this->foreignModel = new $name;
        }
        return $this->foreignModel;
    }

    private function pivotModelInstance() {
        if (is_null($this->pivotModel)) {
            $name = $this->pivotModel();
            $this->pivotModel = new $name;
        }

        return $this->pivotModel;
    }

}
