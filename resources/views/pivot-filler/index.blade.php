@extends('pivot-filler::container')
@section('pivot-filler.content')
    <div class="container" style="padding: 30px 0px;">
        <style>
            .demo {
                border: 1px solid #C0C0C0;
                border-collapse: collapse;
                padding: 5px;
            }

            .demo th {
                border: 1px solid #C0C0C0;
                padding: 5px;
                background: #F0F0F0;
            }

            .demo td {
                border: 1px solid #C0C0C0;
                padding: 5px;
            }
        </style>
        <form method="post" action="{{ $form['action'] }}">
            <input type="submit">
            <div id="all-pivots-form"></div>
        </form>
        <table class="demo">
            <thead>
            <tr>
                <th colspan="3">foreign model</th>
                <th colspan="2">pivot model</th>
                <th colspan="3">local model</th>
                <th colspan="1">actions</th>
            </tr>
            </thead>
            <thead>
            <tr>
                <th>id</th>
                <th>name</th>
                <th>provider value</th>
                <th>foreign value</th>
                <th>local value</th>
                <th>suggested relationship</th>
                <th>id</th>
                <th>name</th>
                <th>store</th>
                <th>new</th>
                <th>store selected</th>
            </tr>
            </thead>
            <tbody>
            @foreach($foreignAssets as $asset)
                <tr>
                    <td>{{$asset->id}}</td>
                    <td>{{$asset['name']}}</td>
                    <td>{{$asset->provider_value}}</td>
                    <td>{{@$asset->pivot->foreign_value}}</td>
                    <td>{{@$asset->pivot->local_value}}</td>
                    <td>
                        @if(!isset($asset->pivot) && !isset($asset->suggested_relationship))
                            <select class="custom-select">
                                <option selected>Choose...</option>
                                @foreach($localAssets as $localAsset)
                                    <option value="{{$localAsset['id']}}">
                                        {{$localAsset['name']}}--{{$localAsset['id']}}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </td>
                    <td>{{@$asset->suggested_relationship->id}}</td>
                    <td>{{@$asset->suggested_relationship->name}}</td>
                    <td>
                        @if(!isset($asset->pivot))
                            <form method="post" action="{{ $form['action'] }}">
                                <div class="input-group mb-3">
                                    <input class="form-control" type="text" name="foreign_value" value="{{$asset->id}}"
                                           style="width:60px">
                                    <input class="form-control" type="text" name="local_value"
                                           value="{{@$asset->suggested_relationship->id}}" style="width:60px">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit">save</button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </td>
                    <td>
                        @if(!isset($asset->pivot) && !isset($asset->suggested_relationship))
                            <form method="post" action="{{ $form['local-action'] }}">
                                <div class="input-group mb-3">
                                    <input type="hidden" name="name" value="{{$asset->name}}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit">create local</button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </td>
                    <td>
                        @if(!isset($asset->pivot))
                            <div class="input-group-text to-save">
                                @if(isset($asset->suggested_relationship->id))
                                    <input type="checkbox"
                                           foreign_value="{{$asset->id}}"
                                           local_value="{{@$asset->suggested_relationship->id}}"
                                           onchange=""
                                           checked>
                                @else
                                    <input type="checkbox"
                                           foreign_value="{{$asset->id}}"
                                           local_value="{{@$asset->suggested_relationship->id}}">
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <script>
        (function () {
            function getPivots() {
                var response = [];
                $('.to-save').each(function (key, value) {
                    var checkbox = $(value).find(':checkbox');
                    if (checkbox.prop('checked') === true) {
                        response.push({
                            foreign_value: checkbox.attr('foreign_value'),
                            local_value: checkbox.attr('local_value'),
                        });
                    }
                });
                return response;
            }

            function updateFormPivots(pivots) {
                $('.pivot-field').remove();
                pivots.forEach(function(value, index){
                    $('#all-pivots-form').append(`
                    <div class="pivot-field">
                        <input type="hidden" name="pivots[${index}][foreign_value]" value="${value.foreign_value}">
                        <input type="hidden" name="pivots[${index}][local_value]"
                               value="${value.local_value}">
                    </div>
                    `);
                });
            }

            function updateGlobalForm(){
                updateFormPivots(getPivots());
            }

            function registerCheckboxChange() {
                $('.to-save :checkbox').change(function(){
                    updateGlobalForm();
                });
            }

            jQuery('body').ready(function () {
                updateGlobalForm();
                registerCheckboxChange();
            });
        })();
    </script>
@endsection
