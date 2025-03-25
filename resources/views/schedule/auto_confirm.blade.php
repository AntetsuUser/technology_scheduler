@extends('layouts.app')
@section('title', '自動化：削除確認')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/confirm.css') }}" rel="stylesheet">
    <script></script>
@else
    <link href="{{ asset('/css/confirm.css') }}" rel="stylesheet">
@endif
@endsection

@section('content')

{{-- 最初の画面に戻る --}}
<div class="browser_back_area container-fluid">
    <div class="row">
        <div class="browser_back_area col-md-1 d-flex align-items-center">
            <a href="#" onclick="history.back(-1);return false;">
                <img class="back_btn" src="{{ asset('../img/icon/back.png') }}" alt="" >
                <span>戻る</span>
            </a>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="confirm">
            <div class="confirm_title">  
                <h1 class="mb-0">以下のデータを削除しますか？</h1>
            </div>

            {{-- チェックされた場所の、ファイル名 / シート名を表示 --}}
            <div>
                @foreach ($ExcelInfo_data as $data)
                    <h2 class="my-5">・{{ $data->file_name }} / {{ $data->sheet_name }}</h2>
                @endforeach
            </div>

            <form class="" action="{{ route('schedule.auto_delete') }}" method="POST" id="deleteTable">
                @csrf

                {{-- チェックされた id の受け渡し（複数ある為） --}}
                @foreach ($checkedValues as $value)
                    <input type="hidden" name="deletes[]" value="{{ $value }}">
                @endforeach

                {{-- データの受け渡し --}}
                <input type="hidden" id="start_date" name="start_date" value="{{ $params['start_date'] }}">
                <input type="hidden" id="end_date" name="end_date" value="{{ $params['end_date'] }}">

                {{-- 削除ボタン --}}
                <div class="w-100">                     
                    {{-- 削除確認画面 --}}
                    <input type="submit" class="btn btn-delete w-100" id="deleteBtn" value="削除"/>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- bootstrap-datepickerを読み込む -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

@endsection