@extends('layouts.app')
@section('title', '自動化')

@section('css')
@if(config('app.env') === 'production')
  <link href="{{ secure_asset('/css/table.css') }}" rel="stylesheet">
  <link href="{{ secure_asset('/css/loading.css') }}" rel="stylesheet">
@else
  <link href="{{ asset('/css/table.css') }}" rel="stylesheet">
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
    <div class="browser_back_area container-fluid">
        <div class="row">

            {{-- 前の画面に戻る --}}
            <div class="browser_back_area col-md-1 d-flex align-items-center">
                <a href="#" onclick="history.back(-1);return false;">
                    <img class="back_btn" src="{{ asset('../img/icon/back.png') }}" alt="" >
                    <span>戻る</span>
                </a>
            </div>
        </div>
        <div>
            <p class="m-0">Excelファイルを読み込みました。</p>
        </div>
    </div>
    
    <div class="scrollBox">

        {{-- データの受け渡し --}}
        <input type="hidden" id="action"name="action" value="{{ $action }}">
        <input type="hidden" id="start_date" name="start_date" value="{{ $params['start_date'] }}">
        <input type="hidden" id="end_date" name="end_date" value="{{ $params['end_date'] }}">

        {{-- テーブル --}}
        <table class="tbl auto_tbl">
            {{-- 生成する個数分追加（js） --}}
            <colgroup>
                <col /><col /><col /><col /><col /><col /><col /><col />
            </colgroup>

            {{-- テーブルのヘッダー --}}
            {{-- 日付のヘッダーの追加（js） --}}
            <thead>
                <tr>
                    <th>No.</th>
                    <th>ファイル名</th>
                    <th>製造課</th>
                    <th>区分</th>
                    <th>工程</th>
                    <th>設備No</th>
                    <th>RB納期</th>
                    <th>担当者</th>
                </tr>
            </thead>
            
            {{-- テーブルの中身（$jsonDataで受け取り） --}}
            <tbody class="count-rowid">
                @if ($jsonData != null)
                    @foreach ($jsonData as $file => $sheets)
                        @foreach ($sheets as $sheetName => $data)
                            @php
                                $row = $data['自動化情報'];
                                $list = $data['自動化リスト'];
                                $complate = $data['完了判定'];

                                // 複数あるのが担当者のみ
                                $maxRows = count($row['担当者']);
                            @endphp

                            @for ($i = 0; $i < $maxRows; $i++)
                                <tr name="{{ $row['ファイル名'] }}-{{ $row['シート名'] }}">
                                    {{-- ズレをなくす為に invisibleCellde で見えない場所も指定 --}}
                                    @if ($i == 0)
                                        {{-- <td class="fixedCell td_item" rowspan="{{ $maxRows }}"><span class="count"></span></td> --}}

                                        {{-- 完了していれば色で分かりやすく --}}
                                        @if($complate == '完了')
                                            <td class="fixedCell td_item complate" rowspan="{{ $maxRows }}">
                                                <span class="count"></span>
                                            </td>
                                        @else
                                            <td class="fixedCell td_item" rowspan="{{ $maxRows }}">
                                                <span class="count"></span>
                                            </td>
                                        @endif

                                        <td class="fixedCell td_item" rowspan="{{ $maxRows }}" name="{{ $row['ファイル名'] }} / {{ $row['シート名'] }}">{{ $row['ファイル名'] }}<br>{{ $row['シート名'] }}</td>
                                        <td class="fixedCell td_item" rowspan="{{ $maxRows }}" name="{{ is_array($row['製造課']) ? implode(', ', $row['製造課']) : $row['製造課'] }}">{{ is_array($row['製造課']) ? implode(', ', $row['製造課']) : $row['製造課'] }}</td>
                                        <td class="fixedCell td_item" rowspan="{{ $maxRows }}" name="{{ $row['区分'] }}">{{ $row['区分'] }}</td>
                                        <td class="fixedCell td_item" rowspan="{{ $maxRows }}" name="{{ $row['工程'] }}">{{ $row['工程'] }}</td>
                                        <td class="fixedCell td_item" rowspan="{{ $maxRows }}" name="{{ $row['設備No'] }}">{{ $row['設備No'] }}</td>
                                    @else
                                        <td class="invisibleCell td_item"></td>
                                        <td class="invisibleCell td_item" name="{{ $row['ファイル名'] }} / {{ $row['シート名'] }}"></td>
                                        <td class="invisibleCell td_item" name="{{ is_array($row['製造課']) ? implode(', ', $row['製造課']) : $row['製造課'] }}"></td>
                                        <td class="invisibleCell td_item" name="{{ $row['区分'] }}"></td>
                                        <td class="invisibleCell td_item" name="{{ $row['工程'] }}"></td>
                                        <td class="invisibleCell td_item" name="{{ $row['設備No'] }}"></td>
                                    @endif

                                    {{-- <td class="fixedCell td_item" name="{{ $row['設備No'][$i] ?? '' }}">{{ $row['設備No'][$i] ?? '' }}</td> --}}

                                    @if ($i == 0)
                                        <td class="fixedCell td_item" rowspan="{{ $maxRows }}" name="{{ $row['RB納期'] }}">{{ $row['RB納期'] }}</td>
                                    @else
                                        <td class="invisibleCell td_item" name="{{ $row['RB納期'] }}"></td>
                                    @endif

                                    <td class="fixedCell td_item" name="{{ $row['担当者'][$i] ?? '' }}">{{ $row['担当者'][$i] ?? '' }}</td>
                                </tr>
                            @endfor

                            {{-- 工程リストの表示部分  --}}
                            {{-- 判定＆表示で使う部分を 配列にしてjsで使いやすくしておく --}}
                            @foreach ($list as $autoName => $autoSteps)
                                @php
                                    $stepIndex = 1;  // 同じ項目の何番目か表示する為 / 初期化
                                    $circleNumber = numberToCircled($autoName); // 漢数字に変換
                                @endphp

                                @foreach ($autoSteps as $stepName => $stepDetails)
                                    @foreach ($stepDetails as $detailsName => $detailsDays)
                                        @php
                                            // {{ dd($detailsDays); }}

                                            $autoData[] = 
                                            [
                                                'ファイル名' => $row['ファイル名'],
                                                'シート名' => $row['シート名'],
                                                'No.' => $circleNumber . '-' . $stepIndex,
                                                '項目' => $stepName,
                                                '作業詳細' => $detailsName,
                                                '予定日' => $detailsDays['予定日'],
                                                '着手日' => $detailsDays['着手日'],
                                                '完了日' => $detailsDays['完了日'],
                                                '納期' => $detailsDays['納期'],
                                                '担当者' => $detailsDays['担当者']
                                            ];

                                            $stepIndex++;
                                        @endphp
                                    @endforeach
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach         
                @endif
                
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

        {{-- ws からのメッセージ or エラーメッセージの表示 --}}
        @if (session('flash_message'))
            <div class="flash_message">
                {{ session('flash_message') }}
            </div>
        @endif
        @if ($errors->has('error'))
            <div class="alert alert-danger">
                {{ $errors->first('error') }}
            </div>
        @endif
    </div>
</div>

<script>
    // DBの日付情報をjsで処理したいため、受け渡し（スケジューラ右部分）
    var autoData = @json($autoData ?? []);

    // 何かイベントがあったときにリロード
    window.addEventListener('pageshow', function(event) 
    {
        if (event.persisted) 
        {
            window.location.reload(true);
        }
    });
</script>

{{-- 使用する js ファイル --}}
@if(config('app.env') === 'production')
    <script src="{{secure_asset('../js/table.js')}}"></script>
    <script src="{{secure_asset('../js/loading.js')}}"></script>
@else
    <script src="{{asset('../js/table.js')}}"></script>
    <script src="{{asset('../js/loading.js')}}"></script>
@endif

@endsection