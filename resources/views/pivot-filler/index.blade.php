@extends('pivot-filler::container')
@section('pivot-filler.content')
    <div class="container" style="padding: 30px 0px;">
        <style>
            .demo {
                border:1px solid #C0C0C0;
                border-collapse:collapse;
                padding:5px;
            }
            .demo th {
                border:1px solid #C0C0C0;
                padding:5px;
                background:#F0F0F0;
            }
            .demo td {
                border:1px solid #C0C0C0;
                padding:5px;
            }
        </style>
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

                    </td>
                    <td>{{@$asset->suggested_relationship->id}}</td>
                    <td>{{@$asset->suggested_relationship->name}}</td>
                    <td>
                        @if(!isset($asset->pivot))
                            <form method="post" action="{{ $form['action'] }}">
                                <input type="text" name="foreign_value" value="{{$asset->id}}">
                                <input type="text" name="local_value" value="{{@$asset->suggested_relationship->id}}">
                                <button type="submit">save</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
