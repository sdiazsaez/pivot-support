<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 5/3/18
 * Time: 16:17
 */

namespace Sura\Vehicles\Http\Controllers\Refactor;

use Illuminate\Http\Request;
use Msd\Vehicles\Models\{
    VehicleMake, VehicleModel, VehicleType
};

use Sura\Vehicles\Models\{
    Vehicle, VehiclePivot
};

class ChunkRefactor {

    private $routePrefix = 'sura.vehicles.chunk-refactor.';
    private $companyAssets;
    private $makesAssets;
    private $makerPivot;

    public function __construct() {
        //$this->companyAssets = $this->getCompanyAssets();
    }

    public function makes() {
        $this->makesAssets = $this->getMakesAssets();
        return view('sura.vehicles.chunk-refactor.index', [
            'form'        => [
                'action' => route($this->routePrefix . 'create-maker')
            ],
            'makesAssets' => $this->makesAssets
        ]);
    }

    public function models($id) {
        $makesAssets = VehiclePivot::Makes()
                                   ->with('msdVehicle')
                                   ->get();

        $make = VehiclePivot::where('msd_vehicle_id', $id)
                            ->with('msdVehicle')
                            ->first();

        $modelAssets = $this->getModelsAssets($make->msd_vehicle_id, $make->value);

        return view('sura.vehicles.chunk-refactor.model', [
            'form'         => [
                'action' => route($this->routePrefix . 'create-pivot-model', [$id])
            ],
            'makesAssets'  => $makesAssets->toArray(),
            'modelsAssets' => $modelAssets,
            'makes'        => $make->toArray()
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
        if (count($models) <= 0) {
            VehicleModel::create($modelInput)
                        ->save();
        }

        return redirect(route($this->routePrefix . 'model-detail', [$id]));
    }

    public function submitMakesPivot(Request $request) {
        $pivotInput = $request->all();

        foreach ($pivotInput as $key => $value) {
            VehiclePivot::create([
                'parent_id'      => 0,
                'msd_vehicle_id' => $value,
                'value'          => $key,
                'msd_type'       => 'vehicle_makes'
            ])
                        ->save();
        }
    }

    public function submitModelPivot(Request $request, $id) {
        $pivotInput = $request->all();
        foreach ($pivotInput as $key => $value) {
            VehiclePivot::create([
                'parent_id'      => $id,
                'msd_vehicle_id' => $value,
                'value'          => $key,
                'msd_type'       => 'vehicle_models'
            ])
                        ->save();
        }


        return redirect(route($this->routePrefix . 'models', [$id]));
    }


    private function getMakesAssets() {
        $makersPivot = VehiclePivot::Makes()
                                   ->get();
        $makersValues = array_column($makersPivot->toArray(), 'value');
        $vehicles = Vehicle::whereNotIn('marca_id', $makersValues)
                           ->get();
        $data = [];
        foreach ($vehicles as $vehicle) {
            if (!array_key_exists($vehicle['marca_id'], $data)) {
                $found = $this->searchMaker($vehicle['marca'])
                              ->toArray();
                if (count($found) > 0) {
                    $data[$vehicle['marca_id']] = [
                        'make'  => $vehicle['marca'],
                        'found' => $found
                    ];
                }
            }
        }

        return $data;
    }

    private function getModelsAssets($parentId, $marcaId) {
        $modelsPivot = VehiclePivot::Models()
                                   ->where('parent_id', $parentId)
                                   ->get();
        $modelsValues = array_column($modelsPivot->toArray(), 'value');
        $vehicles = Vehicle::where('marca_id', $marcaId)
                           ->whereNotIn('modelo_id', $modelsValues)
                           ->get();
        $data = [];
        foreach ($vehicles as $vehicle) {
            if (!array_key_exists($vehicle['modelo_id'], $data)) {
                $found = $this->searchModel($vehicle['modelo'], $parentId, $vehicle['tipo_id'])
                              ->toArray();
                if (count($found) > 0) {
                    $data[$vehicle['modelo_id']] = [
                        'model' => $vehicle,
                        'found' => $found
                    ];
                }
            }
        }

        return $data;
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
                                          ->whre([
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
            return [
                'pivot' => $pivot->toArray(),
                'data'  => $this->searchModel($asset->modelo, $makerId, $asset->tipo_id)
                                ->toArray()
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
                'name' => [
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
                'make_id' => [
                    'tag'   => 'input',
                    'type'  => 'number',
                    'value' => $makerPivotId
                ],
                'name'    => [
                    'tag'   => 'input',
                    'type'  => 'text',
                    'value' => $asset->modelo
                ],
                'type_id' => [
                    'tag'     => 'select',
                    'options' => $options,
                    'keys'    => $keys,
                    'value'   => @$options[0][$keys['value']]
                ],
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
                ]
            ]
        ];
    }

}
