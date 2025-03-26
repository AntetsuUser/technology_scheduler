<!DOCTYPE html>
{{-- <html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head> --}}
{{-- テスト --}}

@extends('layouts.app')
@section('title', '生産管理スケジューラー')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/index.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/index.css') }}" rel="stylesheet">
@endif
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center main_box">
            <div class="col-md-4">
                <div class="sub_box">

                    {{-- <a href="{{ route('schedule.processing') }}" class="btn btn-light main_btn"> --}}
                    <a href="{{ route('item', ['action' => 'process']) }}" class="btn btn-light main_btn">
                    {{-- <a href="{{ route('item', ['action' => 'processing']) }}" class="btn btn-light main_btn"> --}}
                        <img src="./img/icon/process.png" alt="" srcset="">
                        <p>スケジュール（加工）</p>
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="sub_box">
                    {{-- <a href="{{ route('schedule.auto') }}" class="btn btn-light main_btn"> --}}
                    <a href="{{ route('item', ['action' => 'auto']) }}" class="btn btn-light main_btn">
                        <img src="./img/icon/robotarm.png" alt="" srcset="">
                        <p>スケジュール（自動）</p>
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="sub_box">
                    <a href="{{ route('list.list') }}" class="btn btn-light main_btn">
                        <img src="./img/icon/list02.png" alt="" srcset="">
                        <p>一覧表示</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection