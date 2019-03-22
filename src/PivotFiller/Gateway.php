<?php

namespace Larangular\PivotSupport\PivotFiller;

use Illuminate\Http\Request;
use Msd\Vehicles\Models\{
    VehicleMake, VehicleModel, VehicleType
};

use Sura\Vehicles\Models\{
    Vehicle, VehiclePivot
};

class Gateway {

    private $routePrefix = 'sura.vehicles.refactor.';
    private $companyAssets;
    private $makerPivot;

    public function __construct() {
        $this->companyAssets = $this->getCompanyAssets();
    }

    public function index() {
        return view('sura.vehicles.refactor.index', [
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

    public function makerDetail($id) {
        $asset = $this->getCompanyAsset($id);
        $makers = $this->getComparableMaker($asset);

        return view('sura.vehicles.refactor.index', [
            'companyAssets' => $this->companyAssets,
            'companyAsset'  => $asset->toArray(),
            'comparable'    => [
                'makers' => $makers
            ],
            'forms'         => [
                'makers' => $this->makersForm($asset),
                'pivot'  => $this->pivotForm($id, 0, $makers['data'], $asset->marca_id),
            ]
        ]);
    }

    public function modelDetail($id) {
        $asset = $this->getCompanyAsset($id);
        $makers = $this->getComparableMaker($asset);
        //$pivot = $this->getPivot($asset->marca_id);

        if (!isset($this->makerPivot)) {
            return redirect(route($this->routePrefix . 'show', [$id]));
        }

        $models = $this->getComparableModel($asset, $this->makerPivot['msd_vehicle_id']);


        return view('sura.vehicles.refactor.index', [
            'companyAssets' => $this->companyAssets,
            'companyAsset'  => $asset->toArray(),
            'comparable'    => [
                'models' => $models
            ],
            'forms'         => [
                'models' => $this->modelsForm($asset, $this->makerPivot['msd_vehicle_id']),
                'pivot'  => $this->pivotForm($id, $this->makerPivot['msd_vehicle_id'], $models['data'], $asset->modelo_id),
            ]
        ]);
    }

    public function submitMaker(Request $request, $id) {
        $name = $request->get('name');
        $makers = $this->searchMaker($name);
        if (count($makers) <= 0) {
            $maker = new VehicleMake();
            $maker->name = $name;
            $maker->save();
        }

        return redirect(route($this->routePrefix . 'maker-detail', [$id]));
    }

    public function submitModel(Request $request, $id) {
        $modelInput = $request->all();
        $models = $this->searchModel($modelInput['name'], $modelInput['make_id'], $modelInput['type_id']);
        //if (count($models) <= 0) {
        VehicleModel::create($modelInput)
                    ->save();
        //}

        return redirect(route($this->routePrefix . 'model-detail', [$id]));
    }

    public function submitPivot(Request $request, $id) {
        $pivotInput = $request->all();
        $pivot = $this->getPivot($pivotInput['value'], $pivotInput['parent_id']);

        $routeAppend = ($pivotInput['parent_id'] == 0) ? 'maker-detail' : 'model-detail';
        if (count($pivot) <= 0) {
            VehiclePivot::create($pivotInput)
                        ->save();
        }

        return redirect(route($this->routePrefix . $routeAppend, [$id]));
    }

    private function getCompanyAssets() {
        $vehicles = Vehicle::all();
        $data = [];
        $response = [];

        foreach ($vehicles as $vehicle) {
            $key = $vehicle['marca_id'] . $vehicle['modelo_id'];
            if (!array_key_exists($key, $data)) {
                $data[$key] = $vehicle;
            }
        }

        foreach ($data as $vehicle) {
            $makerPivot = VehiclePivot::Makes()
                                      ->where('value', $vehicle['marca_id'])
                                      ->first();

            if (!is_null($makerPivot)) {
                $modelPivot = VehiclePivot::Models()
                                          ->where([
                                              'parent_id' => $makerPivot->msd_vehicle_id,
                                              'value'     => $vehicle['modelo_id'],
                                          ])
                                          ->first();

                if (!is_null($modelPivot)) {
                    continue;
                }
            }
            $response[] = $vehicle;
        }

        return $response;
    }

    private function getCompanyAsset($id) {
        return Vehicle::find($id);
    }

    private function getComparableMaker($asset) {
        $pivot = $this->getPivot($asset->marca_id);
        $this->makerPivot = $pivot->first();
        return [
            'pivot' => $pivot->toArray(),
            'data'  => $this->searchMaker($asset->marca)
                            ->toArray()
        ];
    }

    private function getComparableModel($asset, $makerId) {
        if (isset($this->makerPivot)) {
            $pivot = $this->getPivot($asset->modelo_id, $this->makerPivot['msd_vehicle_id']);
            $data = $this->searchModel($asset->modelo, $makerId);
            if (count($data) <= 0) {
                $name = str_replace(' ', '', $asset->modelo);
                $data = $this->searchModel($name, $makerId);
            }

            return [
                'pivot' => $pivot->toArray(),
                'data'  => $data->toArray()
            ];
        }
    }

    private function searchMaker($name) {
        return VehicleMake::where('name', 'like', '%' . $name . '%')
                          ->get();
    }

    private function getPivot($id, $parentId = 0) {
        return VehiclePivot::where([
            'value'     => $id,
            'parent_id' => $parentId
        ])
                           ->get();
    }

    private function searchModel($name, $makerId, $typeId = false) {
        $search = [
            [
                'name',
                'like',
                '%' . $name . '%'
            ],
            [
                'make_id',
                $makerId
            ]
        ];

        if ($typeId !== false) {
            array_push($search, [
                'type_id',
                $typeId
            ]);
        }

        return VehicleModel::where($search)
                           ->get();
    }


    private function makersForm($asset) {
        return [
            'action' => route($this->routePrefix . 'create-maker', [$asset->id]),
            'fields' => [
                'name'     => [
                    'tag'   => 'input',
                    'type'  => 'text',
                    'value' => $asset->marca
                ],
            ]
        ];
    }

    private function modelsForm($asset, $makerPivotId) {
        $keys = [
            'value' => 'id',
            'label' => 'name'
        ];
        $options = VehicleType::all()
                              ->toArray();
        return [
            'action' => route($this->routePrefix . 'create-model', [$asset->id]),
            'fields' => [
                'make_id'  => [
                    'tag'   => 'input',
                    'type'  => 'number',
                    'value' => $makerPivotId
                ],
                'name'     => [
                    'tag'   => 'input',
                    'type'  => 'text',
                    'value' => $asset->modelo
                ],
                'type_id'  => [
                    'tag'     => 'select',
                    'options' => $options,
                    'keys'    => $keys,
                    'value'   => @$options[0][$keys['value']]
                ]
            ]
        ];
    }

    private function pivotForm($assetId, $parentId, $options, $valueId) {
        $keys = [
            'value' => 'id',
            'label' => 'name'
        ];

        return [
            'action' => route($this->routePrefix . 'create-pivot', [$assetId]),
            'fields' => [
                'parent_id'      => [
                    'tag'   => 'input',
                    'type'  => 'text',
                    'value' => $parentId
                ],
                'msd_vehicle_id' => [
                    'tag'     => 'select',
                    'options' => $options,
                    'keys'    => $keys,
                    'value'   => @$options[0][$keys['value']]
                ],
                'value'          => [
                    'tag'   => 'input',
                    'type'  => 'number',
                    'value' => $valueId
                ],
                'msd_type' => [
                    'tag'   => 'input',
                    'type'  => 'text',
                    'value' => 'vehicle_models'
                ]
            ]
        ];
    }

}
