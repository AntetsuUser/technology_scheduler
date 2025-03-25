// 変更前
$('.start_date, .end_date').datepicker({
    startView: 1,
    format: 'yyyy/m/d',
    language:'ja'
});

$(function(){
    $('.start_date').datepicker() 
    .on('changeDate', function(e){ // 開始日が選択されたら
        // datapicker-switch ボタンを無効化
        // $('tr .datepicker-switch').css('pointer-events', 'none').attr('disabled', true);

        // $('.end_date').datepicker('show'); // 終了日のカレンダーを表示
        $('.start_date').datepicker('hide');
        $('.end_date').attr('disabled',false);
        $('.end_date').attr('readonly',true);
        $('.end_date').val();

        selected_date = e['date']; // 開始日のデータ取得

        yyyy = selected_date.getFullYear();
        mm = selected_date.getMonth() + 1;
        dd = selected_date.getDate();
        sd = computeDate(yyyy, mm, dd, 0); // 開始日そのままのDateオブジェクト
        ed = computeDate(yyyy, mm, dd, 365); // 開始日から1年後のDateオブジェクト

        $('.end_date').datepicker('setStartDate', sd); // start日より前の日を無効化
        $('.end_date').datepicker('setEndDate', ed); // start日から1年後より先の日を無効化
    });

    $('.end_date').datepicker()
    .on('show', function () {
        // end_date に関連付けられた datepicker のみを対象
        let $datepicker = $('.end_date').data('datepicker').picker;
        $datepicker.find('.datepicker-switch').css('pointer-events', 'none').attr('disabled', true);
    });
});

function computeDate(year, month, day, addDays) 
{
    var dt = new Date(year, month - 1, day);
    var baseSec = dt.getTime();
    var addSec = addDays * 86400000; // 日数 * 1日のミリ秒数
    var targetSec = baseSec + addSec;
    dt.setTime(targetSec);

    return dt;
}

$('#date_btn').on('click', function() 
{
    let start = $('.start_date').val();
    let end = $('.end_date').val();

    console.log("開始日" + start + " || 終了日" + end);

    if (endDate > startDate)
    {
        // 日付範囲を求めて配列に入れる関数を呼び出す
        let dateArray = getDatesInRange(start, end);
        alert("中身 : " + dateArray + " || " + dateArray.length + "日間");
    }
    else
    {
        alert("ミス");
    }

    // if (!isValidDate(start) || !isValidDate(end)) 
    // {
    //     event.preventDefault(); // フォーム送信を停止
    //     alert('日付はyyyy/m/dの形式で入力してください。');
    // }
});

// 日付の形式をチェックする関数
function isValidDate(dateString) 
{
    var regex = /^\d{4}\/\d{1,2}\/\d{1,2}$/; // yyyy/m/dの形式の正規表現
    return regex.test(dateString);
}

// 開始日から終了日までの
function getDatesInRange(start, end) 
{
    let startDate = new Date(start);
    let endDate = new Date(end);
    let dateArray = [];

    // console.log(startDate + " / " + endDate);

    while (startDate <= endDate) 
        {
        dateArray.push(formatDate(startDate));
        startDate.setDate(startDate.getDate() + 1);
    }

    return dateArray;
}

// 日付の形式変更
function formatDate(date) 
{
    let year = date.getFullYear();
    let month = date.getMonth() + 1; // 月は0から始まるため1を足す
    let day = date.getDate();

    return `${year}/${month}/${day}`;
    // return `${month}/${day}`;
}


// データが送られる際にhiddenにして送る
// フォームの送信イベント
$('#period_form').submit(function(e) 
{
    // フォームの送信を停止
    e.preventDefault();

    // 入力された日付け取得
    var start_date = $('.start_date').val();
    var end_date = $('.end_date').val();

    // hiddenに値設定
    $('#hidden_start_date').val(start_date);
    $('#hidden_end_date').val(end_date);
    
    // フォームを再送信
    this.submit();
});