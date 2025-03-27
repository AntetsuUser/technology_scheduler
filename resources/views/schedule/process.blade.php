@extends('layouts.app')
@section('title', '生産管理スケジューラー')

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

    {{-- モーダル --}}
    <div id="myModal" class="modal">
        <div class="modal-content">
            {{-- <span id="closeModal">&times;</span> --}}
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
                        <img class="back_btn" src="../img/icon/back.png" alt="">
                        <span>戻る</span>
                    </a>
                </div>

                {{-- 更新、削除ボタン --}}
                <div class="col-md-2 d-flex align-items-center">                        
                    {{-- 更新 --}}
                    <input type="submit" class="btn btn-reload" id="updateBtn" value="更新" formaction="{{ route('update.process') }}">
        
                    {{-- 削除確認画面 --}}
                    <input type="submit" class="btn btn-delete" id="deleteBtn" value="削除" formaction="{{ route('schedule.process_confirm') }}" onsubmit="return delete_check()">

                    {{-- 更新 --}}
                    <input type="submit" class="btn btn-worker" id="" value="担当者画面" formaction="{{ route('schedule.process_worker') }}">

        
                    {{-- データの受け渡し --}}
                    <input type="hidden" id="action"name="action" value="{{ $action }}">
                    <input type="hidden" id="start_date" name="start_date" value="{{ $params['start_date'] }}">
                    <input type="hidden" id="end_date" name="end_date" value="{{ $params['end_date'] }}">
                </div>

                {{-- フィルター --}}
                <div class="col-md-4">
                    <label class="selectbox-2">
                        <p class="mb-0">表示形式：</p>
                        <select name="display_type" id="display_type">
                            <option value="全体" selected>全体</option>
                            <option value="完了済">完了済</option>
                            <option value="未完了">未完了</option>
                        </select>
                    </label>
                
                    <label class="selectbox-2">
                        <p class="mb-0">担当者  : </p>
                        <select name="worker_name" id="worker_name">
                            {{-- <option value="{{ $data->worker_name ?? '' }}" selected>{{ $data->worker_name ?? '指定なし' }}</option> --}}
                            <option value="" selected>指定なし</option>
            
                            {{--配列と取得  --}}
                            @if (is_array($get_workers) || is_object($get_workers))
                                @foreach ($get_workers as $worker)
                                    <option value="{{ $worker['worker'] }}">{{ $worker['worker'] }}</option>
                                @endforeach  
                            @else
                                <option value="">データがありません</option>
                            @endif  
                        </select>
                    </label>
                </div>

                {{-- 矢印の種類説明部分 --}}
                <div class="col-md-5 d-flex align-items-center">
                    <span class="d-flex align-items-center">
                        <img class="color_img mx-2" src=" ../img/plan.png" alt="予定日">予定日
                    </span>
                    <span class="d-flex align-items-center">
                        <img class="color_img mx-2" src="../img/start.png" alt="着手日">着手日
                    </span>
                    <span class="d-flex align-items-center">
                        <img class="color_img mx-2" src="../img/complete.png" alt="完了日">完了日
                    </span>
                    <span class="d-flex align-items-center">
                        <img class="color_img mx-2" src="../img/dead_over.png" alt="納期遅れ">納期遅れ
                    </span>
                    <span class="d-flex align-items-center">
                        <img class="color_img mx-2" src="../img/last.png" alt="納期、最終日">納期、最終日
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
                    <col /><col /><col /><col /><col /><col /><col />
                </colgroup>

                {{-- テーブルのヘッダー --}}
                {{-- 日付のヘッダーの追加（js） --}}
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ファイル名</th>
                        <th>製造課</th>
                        <th colspan="2">設備No</th>
                        <th class="invisibleCell"></th>
                        <th>加工品番</th>
                        <th>担当者</th>
                    </tr>
                </thead>

                {{-- テーブルの中身（$jsonDataで受け取り） --}}
                <tbody class="count-rowid">
                    @foreach ($jsonData as $file => $sheets)
                        @foreach ($sheets as $sheetName => $data)
                            @php
                                $row = $data['工程情報'];
                                $list = $data['工程リスト'];
                                $complate = $data['完了判定'];
                                $id = $data['id'];

                                $maxRows = max(
                                    count($row['機種']),
                                    count($row['設備番号']),
                                    count($row['担当者'])
                                );
                            @endphp

                            @for ($i = 0; $i < $maxRows; $i++)
                                @if ($complate == '完了')
                                    <tr name="{{ $row['ファイル名'] }}-{{ $row['シート名'] }}" data-state="完了">
                                @else
                                    <tr name="{{ $row['ファイル名'] }}-{{ $row['シート名'] }}" data-state="未完了">
                                @endif
                            
                                    {{-- ズレをなくす為に invisibleCellde で見えない場所も指定 --}}
                                    @if ($i == 0)
                                        {{-- 完了していれば色で分かりやすく --}}
                                        @if($complate == '完了')
                                            <td class="fixedCell td_item complate" rowspan="{{ $maxRows }}">
                                                <span class="count"></span>
                                                <input type="checkbox" name="deletes[]" value="{{ $id }}"/>
                                            </td>
                                        @else
                                            <td class="fixedCell td_item" rowspan="{{ $maxRows }}">
                                                <span class="count"></span>
                                                <input type="checkbox" name="deletes[]" value="{{ $id }}"/>
                                            </td>
                                        @endif
                                        
                                        <td class="fixedCell td_item" rowspan="{{ $maxRows }}" name="{{ $row['ファイル名'] }}/{{ $row['シート名'] }}">{{ $row['ファイル名'] }}<br>{{ $row['シート名'] }}</td>
                                        <td class="fixedCell td_item" rowspan="{{ $maxRows }}" name="{{ is_array($row['製造課']) ? implode(', ', $row['製造課']) : $row['製造課'] }}">{{ is_array($row['製造課']) ? implode(', ', $row['製造課']) : $row['製造課'] }}</td>
                                    @else
                                        <td class="invisibleCell td_item" ></td>
                                        <td class="invisibleCell td_item" name="{{ $row['ファイル名'] }}/{{ $row['シート名'] }}"></td>
                                        <td class="invisibleCell td_item" name="{{ is_array($row['製造課']) ? implode(', ', $row['製造課']) : $row['製造課'] }}"></td>
                                    @endif

                                    <td class="fixedCell td_item" name="{{ $row['機種'][$i] ?? '' }}">{{ $row['機種'][$i] ?? '' }}</td>
                                    <td class="fixedCell td_item" name="{{ $row['設備番号'][$i] ?? '' }}">{{ $row['設備番号'][$i] ?? '' }}</td>

                                    @if ($i == 0)
                                        <td class="fixedCell td_item" rowspan="{{ $maxRows }}" name="{{ $row['品番'] }}">
                                            {!! nl2br(e($row['品番'])) !!}
                                        </td>
                                    @else
                                        <td class="invisibleCell td_item" name="{{ $row['品番'] }}">{!! nl2br(e($row['品番'])) !!}</td>
                                    @endif

                                    <td class="fixedCell td_item" name="{{ $row['担当者'][$i] ?? '' }}">{{ $row['担当者'][$i] ?? '' }}</td>
                                </tr>
                            @endfor

                            {{-- 工程リストの表示部分  --}}
                            {{-- 判定＆表示で使う部分を 配列にしてjsで使いやすくしておく --}}
                            @foreach ($list as $processName => $processSteps)
                                @php
                                    $stepIndex = 1;  // 同じ項目の何番目か表示する為 / 初期化
                                    $circleNumber = numberToCircled($processName); // 漢数字に変換
                                @endphp

                                @foreach ($processSteps as $stepName => $stepDetails)
                                    @foreach ($stepDetails as $detailsName => $detailsDays)
                                        @php
                                            $processedData[] = 
                                            [
                                                'ファイル名' => $row['ファイル名'],
                                                'シート名' => $row['シート名'],
                                                'No.' => $circleNumber . '-' . $stepIndex,
                                                '項目' => $stepName,
                                                '詳細' => $detailsName,
                                                '予定日' => $detailsDays['予定日'],
                                                '着手日' => $detailsDays['着手日'],
                                                '完了日' => $detailsDays['完了日'],
                                                '納期' => $detailsDays['納期'],
                                                '担当者' => $detailsDays['担当者'],
                                            ];

                                            $stepIndex++;
                                        @endphp
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
    <script src="{{secure_asset('../js/table.js')}}"></script>
    <script src="{{secure_asset('../js/ajax_filter_process.js')}}"></script>
    <script src="{{secure_asset('../js/loading.js')}}"></script>
@else
    <script src="{{asset('../js/table.js')}}"></script>
    <script src="{{asset('../js/ajax_filter_process.js')}}"></script>
    <script src="{{asset('../js/loading.js')}}"></script>
@endif

@endsection