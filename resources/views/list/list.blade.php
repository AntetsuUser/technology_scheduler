@extends('layouts.app')
@section('title', '一覧')

@section('css')
@if(config('app.env') === 'production')
    
    <link href="{{ secure_asset('/css/list.css') }}" rel="stylesheet">
    {{-- <link href="{{ secure_asset('/css/table.css') }}" rel="stylesheet"> --}}
    <link href="{{ secure_asset('/css/loading.css') }}" rel="stylesheet">
@else
    
    <link href="{{ asset('/css/list.css') }}" rel="stylesheet">
    {{-- <link href="{{ asset('/css/table.css') }}" rel="stylesheet"> --}}
    <link href="{{ asset('/css/loading.css') }}" rel="stylesheet">
@endif
@endsection

@section('content')

{{-- ロード画面 --}}
<div id="loading">
    <div class="sk-cube-grid">
        <div class="sk-cube sk-cube1"></div>
        <div class="sk-cube sk-cube2"></div>
        <div class="sk-cube sk-cube3"></div>
        <div class="sk-cube sk-cube4"></div>
        <div class="sk-cube sk-cube5"></div>
        <div class="sk-cube sk-cube6"></div>
        <div class="sk-cube sk-cube7"></div>
        <div class="sk-cube sk-cube8"></div>
        <div class="sk-cube sk-cube9"></div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        {{-- ホーム画面へ戻る --}}
        <div class="browser_back_area col-md-1">
            <a href="{{ url('/') }}">
                <img class="back_btn" src="../img/icon/back.png" alt="">
                <span>戻る</span>
            </a>
        </div>
    </div>
    
    <div class="row justify-content">
        <div class="table_area">
            {{-- <a href="" class="btn btn-primary main_btn"><p>更新</p></a> --}}
            {{-- <div class="table_div">
                <table border="1" class="table_box"> --}}

                {{-- テーブル全体 --}}
                <div class="scrollBox">
                    {{-- テーブル --}}
                    <table border="1" class="tbl">
                        <thead>
                            <tr>
                                @foreach ($headers as $header)
                                        @if ($loop->first) <!-- 条件を指定 -->
                                            <th class="sticky_cross">{{  $header  }}</th>
                                        @else
                                            <th class="sticky_col">{{ $header }}</th>
                                        @endif
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            {{-- {{ dd($data); }} --}}

                            @foreach ($data as $row)
                                <tr>
                                    @foreach ($row as $cell)
                                        @if ($loop->first)
                                            <td class="sticky_row">{{  $cell  }}</td>
    
                                        @elseif ($loop->index > 0 && $loop->index <= 5)
                                            <td class="sticky_row">{{  $cell  }}</td>
                                        @else
                                            <td>{{ $cell }}</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div> 
        </div>
    </div>
</div>

    <div class="container"> {{-- container --}}
       
    </div>


@if(config('app.env') === 'production')
    <script src="{{secure_asset('../js/loading.js')}}"></script>
@else
    <script src="{{asset('../js/loading.js')}}"></script>
@endif
@endsection