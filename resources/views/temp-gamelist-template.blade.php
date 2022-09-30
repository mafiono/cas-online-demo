@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
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
                            @php
                                $gamesPagination = json_decode($gamesPagination, true);
                            @endphp
                            @foreach($gamesPagination as $game)
                                <div id="game_card" class="col-md">
                                       {{ $game['fullName'] }}
                                       <img class="game_card_img" src="{{ $game['thumbnail'] }}">
                                    </a>
                                </div>
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
