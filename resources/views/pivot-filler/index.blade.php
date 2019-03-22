@extends('pivot-filler')
@section('pivot-filler.content')
    <div class="container" style="padding: 30px 0px;">
        <div class="row">
            <div class="col-3">
                <form method="post" action="{{ $form['action'] }}">
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary mb-2">Submit</button>
                    </div>
                    @foreach($makesAssets as $key => $value)
                        <div style="border:1px solid gray;">
                            <p>{{$key}} - {{$value['make']}}</p>
                            <div>
                                @foreach($value['found'] as $found)
                                    <input type="radio" name="{{$key}}" value="{{$found['id']}}" {{ (strtolower($value['make']) === strtolower($found['name']))? 'checked':'' }}>
                                    <label for="{{$found['id']}}">{{$found['id']}} - {{$found['name']}}</label>
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
