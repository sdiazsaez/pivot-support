@extends('main')
@section('main.content')
    <div class="container" style="padding: 30px 0px;">
        <div class="row">
            <div class="col-3">
                <h3>Marca</h3>
                @includeWhen(isset($makesAssets), 'assets.common.uri-list', ['data' => [
                    'route' => [
                        'name' => 'sura.vehicles.chunk-refactor.models',
                        'params' => []
                    ],
                    'key' => 'msd_vehicle_id',
                    'label' => ['msd_vehicle.name'],
                    'entries' => $makesAssets
                ]])
            </div>
            <div class="col-6">
                {{ $makes['msd_vehicle']['name']  }}
                <form method="post" action="{{ $form['action'] }}">
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary mb-2">Submit</button>
                    </div>
                    @foreach($modelsAssets as $key => $value)
                        <div style="border:1px solid gray;">
                            <p>{{$key}} - {{$value['model']['modelo']}} - {{$value['model']['tipo_id']}}</p>
                            <div>
                                @foreach($value['found'] as $found)
                                    <input type="radio" name="{{$key}}" value="{{$found['id']}}" {{ (strtolower($value['model']['modelo']) === strtolower($found['name']) && $value['model']['tipo_id'] == $found['type_id'])? 'checked':'' }}>
                                    <label for="{{$found['id']}}">{{$found['id']}} - {{$found['name']}} - {{$found['type_id']}}</label>
                                    <br />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </form>
            </div>
        </div>
    </div>
@endsection
