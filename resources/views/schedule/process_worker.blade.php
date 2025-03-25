@extends('layouts.app')
@section('title', '生産管理スケジューラー')

@section('css')
@if(config('app.env') === 'production')
  <link href="{{ secure_asset('/css/worker.css') }}" rel="stylesheet">
  <link href="{{ secure_asset('/css/loading.css') }}" rel="stylesheet">
@else
  <link href="{{ asset('/css/worker.css') }}" rel="stylesheet">
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

{{-- メインの画面 --}}
<div class="tableBase">

    {{-- モーダル --}}
    <div id="myModal" class="modal">
        <div class="modal-content">
            <p>
                現在、更新中です。<br>
                しばらくお待ちください。
            </p>
        </div>
    </div>

    {{-- checkbox もあるのでまとめて囲っておく --}}
    <form class="" action="#" method="POST" id="updateForm">
        @csrf

        <div class="container-fluid">
            <div class="row">

                {{-- ホーム画面へ戻る --}}
                <div class="browser_back_area col-md-1">
                    <a href="{{ url('/') }}">
                        <img class="back_btn" src="/img/icon/back.png" alt="">
                        <span>戻る</span>
                    </a>
                </div>

                {{-- 画面移動ボタン --}}
                <div class="col-md-2 d-flex align-items-center">                        
                    {{-- 元画面へ --}}
                    <a href="#" onclick="history.back(-1);return false;" class="btn btn-worker">
                        <span>元画面へ</span>
                    </a>
        
                    {{-- データの受け渡し --}}
                    {{-- <input type="hidden" id="action"name="action" value="{{ $action }}"> --}}
                    <input type="hidden" id="start_date" name="start_date" value="{{ $params['start_date'] }}">
                    <input type="hidden" id="end_date" name="end_date" value="{{ $params['end_date'] }}">
                </div>

                {{-- フィルター --}}
                <div class="col-md-4">
                    {{-- 担当者の絞り込み（絞り込まない） --}}
                    {{-- <label class="selectbox-2">
                        <p class="mb-0">担当者  : </p>
                        <select name="worker_name" id="worker_name">
                            <option value="" selected>指定なし</option>
                            @if (is_array($get_workers) || is_object($get_workers))
                                @foreach ($get_workers as $worker)
                                    <option value="{{ $worker['worker'] }}">{{ $worker['worker'] }}</option>
                                @endforeach  
                            @else
                                <option value="">データがありません</option>
                            @endif  
                        </select>
                    </label> --}}
                </div>

                {{-- 矢印の種類説明部分 --}}
                <div class="col-md-5 d-flex align-items-center">
                    <span class="d-flex align-items-center">
                        <img class="color_img mx-2" src=" /img/plan.png" alt="予定日">予定日
                    </span>
                    <span class="d-flex align-items-center">
                        <img class="color_img mx-2" src="/img/start.png" alt="着手日">着手日
                    </span>
                    <span class="d-flex align-items-center">
                        <img class="color_img mx-2" src="/img/complete.png" alt="完了日">完了日
                    </span>
                    <span class="d-flex align-items-center">
                        <img class="color_img mx-2" src="/img/dead_over.png" alt="納期遅れ">納期遅れ
                    </span>
                    <span class="d-flex align-items-center">
                        <img class="color_img mx-2" src="/img/last.png" alt="納期、最終日">納期、最終日
                    </span>
                </div>
            </div>
        </div>
        
        {{-- テーブル全体 --}}
        <div class="scrollBox">   
            {{-- テーブル --}}
            <table class="tbl">
                {{-- 生成する個数分追加（js） --}}
                <colgroup>
                    <col /><col />
                </colgroup>

                {{-- テーブルのヘッダー --}}
                {{-- 日付のヘッダーの追加（js） --}}
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>担当者</th>
                    </tr>
                </thead>

                <tbody class="count-rowid">
                    {{-- 担当者のデータを分解して、テーブル化 --}}
                    @foreach ($workerData as $name => $files)
                        <tr name="{{ $name }}" data-state="未完了">
                            <td rowspan ="1" class="fixedCell td_item"></td>
                            <td rowspan ="1" class="fixedCell td_item" >{{ $name }}</td>

                        @foreach ($files as $file => $names)
                            @foreach ($names as $task_num => $steps)
                                @php
                                    $taskNumIndex = 1;  // 同じ項目の何番目か表示する為 / 初期化
                                    $circleNumber = numberToCircled($task_num); // 漢数字に変換
                                @endphp
                                @foreach ($steps as $step => $details)
                                    @foreach ($details as $detail => $dates)
                                        @php
                                            $workerRemakeData[] = 
                                            [
                                                "担当者" => $name,
                                                "No." => $circleNumber . "-" . $taskNumIndex,
                                                '項目' => $step,
                                                '詳細' => $detail,
                                                '予定日' => $dates['予定日'],
                                                '着手日' => $dates['着手日'],
                                                '完了日' => $dates['完了日'],
                                                '納期' => $dates['納期']
                                            ];
                                            $taskNumIndex++;
                                        @endphp

                                        {{-- {{ dump($name." / " . $task_num . " / " . $step . " / " . $detail . " || " . $dates["予定日"]); }} --}}
                                    @endforeach
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach

                    @php
                        // 丸数字に変換
                        function numberToCircled($number) 
                        {
                            if ($number <= 20) 
                            {
                                $base = mb_ord('①', 'UTF-8');
                                return mb_chr($base + $number - 1, 'UTF-8');
                            }
                            elseif ($number <= 35)
                            {
                                $base = mb_ord('㉑', 'UTF-8');
                                return mb_chr($base + $number - 21, 'UTF-8');
                            }
                            elseif ($number <= 50)
                            {
                                $base = mb_ord('㊱', 'UTF-8');
                                return mb_chr($base + $number - 36, 'UTF-8');
                            }

                            // 50以上は対応する丸数字がないため、そのまま数値を返す
                            return $number;
                        }
                    @endphp
                </tbody>
            </table>
        </div>
    </form>

    {{-- バリデーションでのエラーメッセージの表示 --}}
    @if ($errors->has('deletes'))
        <div class="noneData alert alert-danger">
            <p class="text-center">{{ $errors->first('deletes') }}</p>
        </div>
    @endif

    {{-- jsonData 無い、取得できない場合にエラーメッセージを表示 --}}
    @if (empty($jsonData))
        <div class="noneData alert alert-danger">
            <p class="text-center">対応するデータがありません。</p>
        </d>
    @endif
</div>

<script>
    // DBの日付情報をjsで処理したいため、受け渡し（スケジューラ右部分）
    var processedData = @json($processedData ?? []);
    var workerRemakeData = @json($workerRemakeData ?? []);

    // 何かイベントがあったときにリロード
    window.addEventListener('load', function() {
        if (!sessionStorage.getItem('reloaded')) 
        {
            sessionStorage.setItem('reloaded', 'true');     // フラグをセット
            window.location.reload();                       // 1回だけリロード
        }
    });

    // ページキャッシュから復元された場合にリロード
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) 
        {
            sessionStorage.removeItem('reloaded');          // キャッシュ復元時はフラグをリセット
            window.location.reload();
        }
    });
   
    // checkboxが選択されているか判定 
    function delete_check()
    {
        const checkboxes = document.querySelectorAll('input[name="deletes[]"]:checked');
        if (checkboxes.length > 0)
        {
            $('.delete_data').show();
            return true;
        }
        else
        {
            $('.delete_data').hide();
            return false;
        }
    }
</script>

{{-- 使用する js ファイル --}}
@if(config('app.env') === 'production')
    <script src="{{secure_asset('../js/worker.js')}}"></script>
    <script src="{{secure_asset('../js/ajax_filter_process.js')}}"></script>
    <script src="{{secure_asset('../js/loading.js')}}"></script>
@else
    <script src="{{asset('../js/worker.js')}}"></script>
    <script src="{{asset('../js/ajax_filter_process.js')}}"></script>
    <script src="{{asset('../js/loading.js')}}"></script>
@endif

@endsection