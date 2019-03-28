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
    private $foreignGateway;

    public abstract function routePrefix();

    public abstract function localModel();

    public abstract function foreignModel();

    public abstract function pivotModel();

    public abstract function foreignGateway();


    public function __construct() {
        //$this->foreignAssets = $this->getCompanyAssets();
        $this->localAssets = $this->getLocalAssets();
        $this->routePrefix = $this->routePrefix();
    }

    public function index(Request $request) {
        $this->foreignAssets = $this->getCompanyAssets($request->all());

        return view('pivot-filler::index', [
            'foreignAssets' => $this->foreignAssets,
            'localAssets'   => $this->localAssets,
            'form'          => $this->getFormOptions(),
            'tableColumns' => $this->getTableColumns(),
        ]);
    }

    public function localModelStore(Request $request) {
        if ($request->has('name')) {
            $this->localModelInstance()
                 ->create([
                     'name' => $request->get('name'),
                 ])
                 ->save();
        }
        return redirect(route($this->routePrefix . '.pivot-filler.index'));
    }

    public function store(Request $request) {
        $data = $request->all();
        if ($request->has('pivots')) {
            foreach ($data['pivots'] as $pivot) {
                $this->pivotStore($pivot);
            }
        } else {
            $this->pivotStore($data);
        }
        return redirect(route($this->routePrefix . '.pivot-filler.index'));
    }

    private function pivotStore($data) {
        if (!array_key_exists('foreign_value', $data) || !array_key_exists('local_value', $data)) {
            return;
        }

        $foreignValue = $data['foreign_value'];
        $localValue = $data['local_value'];

        $this->pivotModelInstance()
             ->create([
                 'foreign_value' => $foreignValue,
                 'local_value'   => $localValue,
             ])
             ->save();
    }

    private function getCompanyAssets(array $filter) {
        return $this->foreignGatewayInstance()->entries($filter);
        /*
        return $this->foreignModelInstance()
                    ->all();
        */
    }

    private function getLocalAssets() {
        return $this->localModelInstance()
                    ->all();
    }

    private function getFormOptions() {
        return [
            'local-action' => route($this->routePrefix . '.pivot-filler.create-local'),
            'action'       => route($this->routePrefix . '.pivot-filler.store'),
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

    private function foreignGatewayInstance() {
        if (is_null($this->foreignGateway)) {
            $name = $this->foreignGateway();
            $this->foreignGateway = new $name;
        }
        return $this->foreignGateway;
    }

    private function pivotModelInstance() {
        if (is_null($this->pivotModel)) {
            $name = $this->pivotModel();
            $this->pivotModel = new $name;
        }

        return $this->pivotModel;
    }


    private function getTableColumns(): array {
        return [
            'foreign-model' => $this->getForeignModelAttributes(),
            'pivot-model' => $this->getPivotModelAttributes(),
            'local-model' => $this->getLocalModelAttributes()
        ];
    }

    private function getForeignModelAttributes(): array {
        $response = $this->foreignGatewayInstance()->entry(1);
        return array_keys($response->toArray());
    }

    private function getPivotModelAttributes(): array {
        $response = $this->pivotModelInstance()->where(['local_value' => 1])->first();
        if(is_null($response)) {
            return [
                'foreign_value',
                'local_value'
            ];
        }
        return array_keys($response->toArray());
    }

    private function getLocalModelAttributes(): array {
        $response = $this->localModelInstance()->where('id', 1)->first();
        return array_keys($response->toArray());
    }
}
