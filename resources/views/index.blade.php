@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="row justify-content-center">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @auth
                    <p><b>{{ __('Welcome, '.auth()->user()->name) }}</b></p>

                    <div class="container">
                        <div class="row">
                            @foreach($gamesPagination as $game)
                            <a class="col-md" href="/play/{{ $game->game_id }}" target="_blank">
                                <div id="game_card">
                                        <img style="object-fit: fill;" class="game_card_img" src="{{ $game->thumbnail_ext }}" style="max-width: 170px;">
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>                
                    @else
                    {{ __('You are not logged in, log in to start playing the best casino.') }}
                    @endauth



                </div>


                
            </div>
        </div>
    </div>
</div>
@endsection
