@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="row justify-content-center">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                        @if(Session::has('error'))
                            <p class="alert alert-danger">{{ Session::get('error') }}</p>
                        @else
                      <div style="position:relative;padding-top:56.25%;">
                        <iframe src="/launcher?game_id={{ $game }}" frameborder="0" allowfullscreen
                          style="position:absolute;top:0;left:0;width:100%;height:100%;"></iframe>
                      </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
