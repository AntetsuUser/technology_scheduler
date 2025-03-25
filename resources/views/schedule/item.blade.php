@extends('layouts.app')
@section('title', '生産管理スケジューラー')

@section('css')
@if(config('app.env') === 'production')
    <link href="{{ secure_asset('/css/item.css') }}" rel="stylesheet">
@else
    <link href="{{ asset('/css/item.css') }}" rel="stylesheet">
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
            <div class="box">
                <div class="subBox">
                    {{-- post、getの切り替え --}}
                    {{-- process or auto --}}
                    <form action="{{ route('schedule.' . $action)  }}" method="GET">
                        <input type="hidden" name="action" value="{{ $action }}">

                        @csrf
                        <div class="start">
                            <p>開始日：</p>
                            <input type="text" class="form-control start_date" name="start_date" value="{{ old('start_date') }}" autocomplete="off" placeholder="0000/00/00" readonly>
                            {{-- <input type="hidden" id="hidden_start_date" name="hidden_start_date" value=""> --}}
                            
                            @error('start_date')
                                <div class="my-2 error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="end">
                            <p>終了日：</p>
                            <input type="text" class="form-control end_date" name="end_date" value="{{ old('end_date') }}" autocomplete="off" placeholder="0000/00/00" readonly >
                            {{-- <input type="hidden" id="hidden_end_date" name="hidden_end_date"> --}}
                            
                            @error('end_date')
                                <div class="my-2 error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>                     
                            <input type="submit" class="btn trans_btn submit_btn" id="date_btn" value="期間指定">
                        </div>
                    </form>
                </div>
            </div>
    </div>    
</div>

<!-- bootstrap-datepickerを読み込む -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
{{-- カレンダー部分のCSS --}}
<link rel="stylesheet" type="text/css" href="css/bootstrap-datepicker.min.css">
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/bootstrap-datepicker.ja.min.js"></script>
<script src="js/item.js"></script>
{{-- <script src="js/table.js"></script> --}}

@endsection