$(function()
{
    // 指定された日付の範囲内に、今日の日付が無い時の判定（true => 範囲以上, false => 範囲以下） 
    var notTodayState = false;
    var atatus = "";

    $(document).ready(function() 
    {
        // location.reload();

        // 完了日が納期を超えている、納期を過ぎても完了日が入力されていないときにtrue
        var isOverDead = false;

        // 開始日、納期データ取得
        let start = $('#start_date').val();
        let end = $('#end_date').val();
        let action = $('#action').val();

        // 日付範囲を求めて配列に入れる関数を呼び出す
        let dateArray = getDatesInRange(start, end);
        console.log(dateArray.length + "日間");

        console.log("アクション");
        console.log(action);

        if (action == 'process')
        {
            process(dateArray, end);
        } 
        else if (action == 'auto')
        {
            auto(dateArray, end);
        }

        // process(dateArray, end);

        // ホバー処理
        hover();

        // // リロードを一度だけ行うためのフラグをsessionStorageで管理
        // if (sessionStorage.getItem('reloaded') === null) {
        //     // まだリロードされていなければリロードを行う
        //     sessionStorage.setItem('reloaded', 'true');
        //     location.reload();
        // } else {
        //     // リロード後はフラグをクリア
        //     sessionStorage.removeItem('reloaded');
        // }
    });

    // process 処理 ==============================================================================================================
    function process(dateArray, end)
    {
        atatus = "process";

        // php側で取得したデータを格納（jsonを配列にしてある）
        var plans = processedData;
        
        // 綺麗に生成されるようにgroupを作っておく
        let createCol = $("colgroup");
        // headderを生成しやすくするため、格納
        let headerRow = $(".tbl thead tr");

        // 期間中の日付け 
        var fullDates = [];

        // YYYY-MM-DD形式で今日の日付を取得
        var today = new Date().toLocaleDateString('sv-SE');

        // 今日の日付があるかの判定
        var todayState = false;
        
        var endDateObj = new Date(end);
        endDate = formatDate(endDateObj);
        end = endDate['fullDate'];

        // 日付に対応した分のthを作成する
        dateArray.forEach(function(date) 
        {
            // YYYY-MM-DD形式の方だけfullDatesに格納し直し
            fullDates.push(date.fullDate);

            // Dateオブジェクトを作成して曜日を取得
            const currentDate = new Date(date.fullDate);
            const dayOfWeek = currentDate.getDay();

            // クラスを追加しやすい様に格納しておく
            let thClass = 'dateCell';

            if (dayOfWeek === 0)        // 日曜日
            {
                thClass += ' sunday';
            }
            else if (dayOfWeek === 6)   // 土曜日
            {
                thClass += ' saturday';
            }
            else                        // 平日、その他（祝日込み）
            {
                thClass += ' weekday'; 
            }

            // 今日の日付がある場合、色を付けるため
            if (date.fullDate == today) 
            {
                thClass += ' today';
            }

            createCol.append('<col />');
            headerRow.append(`<th class="${thClass}" name="${date.fullDate}">${date.shortDate}</th>`);
        });

        // 入力された範囲内に今日の日付があるか
        var todayIdx = getDateIndex(fullDates, today);

        // 今日の日付があるか
        if (todayIdx != -1)
        {
            todayState = true;
        }
        else
        {
            todayState = false;
            notToday(today, end);
        }

        // 一旦中身を全部初期化する
        var rows = Array(fullDates.length).fill(0).map(() => []);
        
        // シート名の初期化、行番号の初期化
        let previousName = null;
        let row_counter = 0;

        // // 研修用の最終日「
        // end = "2024-01-31";

        // 
        plans.forEach(function(plan, index) 
        {
            // 予定日、開始日、完了日、納期
            // 日付チェックをいれる（不必要データでバグが起こさないように）
            var planIdx = isValidDateFormat(plan["予定日"]) ? getDateIndex(fullDates, plan["予定日"]) : -1;
            var startIdx = isValidDateFormat(plan["着手日"]) ? getDateIndex(fullDates, plan["着手日"]) : -1;
            var complateIdx = isValidDateFormat(plan["完了日"]) ? getDateIndex(fullDates, plan["完了日"]) : -1;
            var deadIdx = isValidDateFormat(plan["納期"]) ? getDateIndex(fullDates, plan["納期"]) : -1;

            // var planIdx = getDateIndex(fullDates, plan["予定日"]);
            // var startIdx = getDateIndex(fullDates, plan["着手日"]);
            // var complateIdx = getDateIndex(fullDates, plan["完了日"]);
            // var deadIdx = getDateIndex(fullDates, plan["納期"]);

            var endIdx = getDateIndex(fullDates, end);
            var sheetName = plan["ファイル名"] + "-" + plan["シート名"];

            // 予定日 or 納期が入力されていなかったらスキップ（矢印が作れない為）
            if (planIdx < 0 || deadIdx < 0) return;

            // 矢印を作成するための最終日
            var lastDayIdx; 

            // 行数の初期化
            var row = 0;
            
            // 遅延の状態
            var isLate = false;

            // シート名が変わったらrowsを初期化    
            if (previousName != sheetName)
            {                
                if (previousName != null)
                {           
                    // シートごとにデータを管理したいので初期化する
                    rows = Array(fullDates.length).fill(0).map(() => []);

                    // console.log("現在のシート : " + sheetName);
                }
                previousName = sheetName;
            };

            // 納期よりも完了日が後だったら（矢印の先端を完了日にする）
            if (deadIdx < complateIdx)
            {      
                lastDayIdx = complateIdx;
                isLate = true;

                // console.log(sheetName + " || " + plan["No."] +" の完了日遅れてる");
            }
            // 今日の日付がある && 納期よりも今日の日付が後 && 完了日が未入力 || 今日の日付がある && 納期よりも今日の日付が後 && 納期が完了日よりも後
            else if (todayState && deadIdx < todayIdx && complateIdx == -1 || todayState && deadIdx < todayIdx && deadIdx < complateIdx)
            {
                lastDayIdx = todayIdx;
                isLate = true
                console.log(sheetName + " || " + plan["No."] +" の完了日未入力 または 遅れてる");
            }
            // 今日の日付が範囲外、範囲越え && 完了日未入力 || 今日の日付が範囲外、範囲越え && 納期が完了日よりも後
            else if(notTodayState && complateIdx == -1 || notTodayState && deadIdx < complateIdx)
            {
                lastDayIdx = endIdx;
                isLate = true;
            }
            // 予定日～納期で矢印を作成
            else
            {
                lastDayIdx = deadIdx;
                isLate = false;
            }

            // データが重なっているか計算、重なっていたら次の行へ
            while (rows.slice(planIdx, lastDayIdx + 1).some(arr => arr.includes(row))) 
            {
                row++;
            }

            // データを追加
            for (var i = planIdx; i <= lastDayIdx; i++) 
            {
                rows[i].push(row);
            }

            // plan.nameの何番目の行か
            var tr = $('tbody tr[name="' + sheetName + '"]').eq(row);

            // 今あるtrの数
            trCounter = $('tbody tr').length;

            // データに対応した数字がないと何も表示されないかも ============================= 
            // 各trに対応するidを振り分ける
            $('tbody tr').each(function(index, element) 
            {
                $(element).attr('id', 'tr-' + index);
            });

            // trがなかったら、対応したtr、tdを作成
            if (tr.length === 0) 
            {
                // console.log(sheetName + " の tr が不足、追加します");

                var allTrs = $('tr[name="'+ sheetName +'"]');
                var firstTr = allTrs.first();
                var lastTr = allTrs.last();
                var tds = firstTr.find('td');

                // 最初の<tr>内の全ての<td>要素を選択（rowspnaの判定のため）
                var tds = firstTr.find('td');

                // 各<td>要素のrowspanを+1する（追加行を結合させるため）
                tds.each(function() 
                {
                    var rowspan = $(this).attr('rowspan');
                    if (rowspan) 
                    {
                        // rowspanが存在する場合は数値として+1する
                        $(this).attr('rowspan', parseInt(rowspan, 10) + 1);
                    }
                });

                // blade側で作成されている tr の data-state を読み取る
                var complateState = ($(firstTr).data('state'));

                // noneはrowspanで被ったときに、ズレるのを防ぐため
                var newTr = $('<tr name="'+ sheetName +'" id="tr-' + trCounter + '" data-state="'+ complateState +'"><td class="td_item invisibleCell"></td><td class="td_item invisibleCell"></td><td class="td_item invisibleCell"></td><td class="td_item fixedCell"></td><td class="td_item fixedCell"></td><td class="td_item invisibleCell"></td><td class="td_item fixedCell"></td></tr>');
                trCounter++;
                newTr.insertAfter(lastTr);
                tr = newTr;
            };

            // trの数ぶん足りない、空のtdを追加する
            $("tbody tr").each(function() 
            {
                var tr = $(this);
                while (tr.find('td').length < fullDates.length + 7) 
                {
                    tr.append('<td class="moveCell blank"></td>');
                }
            });

            // データ挿入（セル内に表示する）
            for (var i = planIdx + 7; i <= lastDayIdx + 7; i++) 
            {
                var $cell = tr.find('td').eq(i);
                var $div = $('<div class="bodyArrow"></div>');
                // console.log("plan, No. : " + plan["No."]);
                
                var circleNum = plan["No."];

                $cell.attr('name', plan["項目"] + " / " + plan["詳細"] );
                $cell.addClass('arrows');

                // 完了日が入力されていたら、矢印の背景色自体変える
                if (complateIdx != -1)
                {
                    $cell.addClass('complate');
                }
                
                // 矢印追加（生成）
                $cell.append($div);

                // 遅れている場合
                if (isLate)
                {
                    // 矢印の種類の判定
                    if (i == planIdx + 7)               // 予定日
                    {
                        $div.addClass('plan');
                        $div.text(circleNum);
                    }
                    else if (i == startIdx + 7)         // 着手日
                    {   
                        $div.addClass('start');
                    }
                    else if (i >= deadIdx + 7 && i < lastDayIdx + 7) // 納期超えてる
                    {
                        $div.addClass('late');
                    }
                    else if (i == lastDayIdx + 7)       // 矢印の最終日
                    {
                        $div.addClass('headArrow');
                        var  $arrowDiv = $('<div class="outerArrow"><div class="innerArrow late"></div></div>');
                        $div.append($arrowDiv);
                    }
                    else
                    {
                        $div.addClass('safe');
                    }
                }
                else // 期限通りの場合
                {
                    // 矢印の種類の判定
                    if (i == planIdx + 7)               // 予定日
                    {
                        $div.addClass('plan');
                        $div.text(circleNum);
                    }
                    else if (i == startIdx + 7)         // 着手日
                    {   
                        $div.addClass('start');
                    }
                    else if (i == deadIdx + 7)          // 納期
                    {
                        // 矢印
                        $div.addClass('headArrow');
                        var  $arrowDiv = $('<div class="outerArrow"><div class="innerArrow safe"></div></div>');
                        $div.append($arrowDiv);
                    }
                    else if (i == complateIdx + 7)      // 完了日
                    {   
                        $div.addClass('complate');
                    }
                    else                                // 矢印中の何もない日
                    {
                        $div.addClass('safe');
                    }
                }
            }    
        });
    }

    // auto 処理 ==============================================================================================================
    function auto(dateArray, end)
    {
        atatus = "auto";

        // php側で取得したデータを格納（jsonを配列にしてある）
        var plans = autoData;

        console.log("取得");
        console.log(plans);
        
        // 綺麗に生成されるようにgroupを作っておく
        let createCol = $("colgroup");
        // headderを生成しやすくするため、格納
        let headerRow = $(".tbl thead tr");

        // 期間中の日付け 
        var fullDates = [];

        // YYYY-MM-DD形式で今日の日付を取得
        var today = new Date().toLocaleDateString('sv-SE');

        // 今日の日付があるかの判定
        var todayState = false;
        
        var endDateObj = new Date(end);
        endDate = formatDate(endDateObj);
        end = endDate['fullDate'];

        // 日付に対応した分のthを作成する
        dateArray.forEach(function(date) 
        {
            // YYYY-MM-DD形式の方だけfullDatesに格納し直し
            fullDates.push(date.fullDate);

            // Dateオブジェクトを作成して曜日を取得
            const currentDate = new Date(date.fullDate);
            const dayOfWeek = currentDate.getDay();

            // クラスを追加しやすい様に格納しておく
            let thClass = 'dateCell';

            if (dayOfWeek === 0)        // 日曜日
            {
                thClass += ' sunday';
            }
            else if (dayOfWeek === 6)   // 土曜日
            {
                thClass += ' saturday';
            }
            else                        // 平日、その他（祝日込み）
            {
                thClass += ' weekday'; 
            }

            // 今日の日付がある場合、色を付けるため
            if (date.fullDate == today) 
            {
                thClass += ' today';
            }

            createCol.append('<col />');
            headerRow.append(`<th class="${thClass}" name="${date.fullDate}">${date.shortDate}</th>`);
        });

        // 入力された範囲内に今日の日付があるか
        var todayIdx = getDateIndex(fullDates, today);

        // 今日の日付があるか
        if (todayIdx != -1)
        {
            todayState = true;
            console.log("ある");
        }
        else
        {
            todayState = false;
            notToday(today, end);
            console.log("なす");
        }

        // 一旦中身を全部初期化する
        var rows = Array(fullDates.length).fill(0).map(() => []);
    
        // シート名の初期化、行番号の初期化
        let previousName = null;
        let row_counter = 0;

        // // 検証の最終日
        // end = "2024-01-31";

        console.log("autoData");
        console.log(plans);

        plans.forEach(function(plan, index) 
        {
            // 予定日、開始日、完了日、納期
           // 予定日、開始日、完了日、納期
            // 日付チェックをいれる（不必要データでバグが起こさないように）
            var planIdx = isValidDateFormat(plan["予定日"]) ? getDateIndex(fullDates, plan["予定日"]) : -1;
            var startIdx = isValidDateFormat(plan["着手日"]) ? getDateIndex(fullDates, plan["着手日"]) : -1;
            var complateIdx = isValidDateFormat(plan["完了日"]) ? getDateIndex(fullDates, plan["完了日"]) : -1;
            var deadIdx = isValidDateFormat(plan["納期"]) ? getDateIndex(fullDates, plan["納期"]) : -1;

            // var planIdx = getDateIndex(fullDates, plan["予定日"]);
            // var startIdx = getDateIndex(fullDates, plan["着手日"]);
            // var complateIdx = getDateIndex(fullDates, plan["完了日"]);
            // var deadIdx = getDateIndex(fullDates, plan["納期"]);

            var endIdx = getDateIndex(fullDates, end);
            var sheetName = plan["ファイル名"] + "-" + plan["シート名"];

            console.log(sheetName);

            // 予定日 or 納期が入力されていなかったらスキップ（矢印が作れない為）
            if (planIdx < 0 || deadIdx < 0) return;

            // 矢印を作成するための最終日
            var lastDayIdx; 

            // 行数の初期化
            var row = 0;
            
            // 遅延の状態
            var isLate = false;

            // シート名が変わったらrowsを初期化    
            if (previousName != sheetName)
            {                
                if (previousName != null)
                {           
                    // シートごとにデータを管理したいので初期化する
                    rows = Array(fullDates.length).fill(0).map(() => []);

                    // console.log("現在のシート : " + sheetName);
                }
                previousName = sheetName;
            };

            // 納期よりも完了日が後だったら（矢印の先端を完了日にする）
            if (deadIdx < complateIdx)
            {      
                lastDayIdx = complateIdx;
                isLate = true;

                // console.log(sheetName + " || " + plan["No."] +" の完了日遅れてる");
            }
            // 今日の日付がある && 納期よりも今日の日付が後 && 完了日が未入力 || 今日の日付がある && 納期よりも今日の日付が後 && 納期が完了日よりも後
            else if (todayState && deadIdx < todayIdx && complateIdx == -1 || todayState && deadIdx < todayIdx && deadIdx < complateIdx)
            {
                lastDayIdx = todayIdx;
                isLate = true
                console.log(sheetName + " || " + plan["No."] +" の完了日未入力 または 遅れてる");
            }
            // 今日の日付が範囲外、範囲越え && 完了日未入力 || 今日の日付が範囲外、範囲越え && 納期が完了日よりも後
            else if(notTodayState && complateIdx == -1 || notTodayState && deadIdx < complateIdx)
            {
                lastDayIdx = endIdx;
                isLate = true;
            }
            // 予定日～納期で矢印を作成
            else
            {
                lastDayIdx = deadIdx;
                isLate = false;
            }

            // データが重なっているか計算、重なっていたら次の行へ
            while (rows.slice(planIdx, lastDayIdx + 1).some(arr => arr.includes(row))) 
            {
                row++;
            }

            // データを追加
            for (var i = planIdx; i <= lastDayIdx; i++) 
            {
                rows[i].push(row);
            }

            // plan.nameの何番目の行か
            var tr = $('tbody tr[name="' + sheetName + '"]').eq(row);

            // 今あるtrの数
            trCounter = $('tbody tr').length;

            // データに対応した数字がないと何も表示されないかも ============================= 
            // 各trに対応するidを振り分ける
            $('tbody tr').each(function(index, element) 
            {
                $(element).attr('id', 'tr-' + index);
            });

            // trがなかったら、対応したtr、tdを作成
            if (tr.length === 0) 
            {
                // console.log(sheetName + " の tr が不足、追加します");

                var allTrs = $('tr[name="'+ sheetName +'"]');
                var firstTr = allTrs.first();
                var lastTr = allTrs.last();
                var tds = firstTr.find('td');

                // 最初の<tr>内の全ての<td>要素を選択（rowspnaの判定のため）
                var tds = firstTr.find('td');

                // 各<td>要素のrowspanを+1する（追加行を結合させるため）
                tds.each(function() 
                {
                    var rowspan = $(this).attr('rowspan');
                    if (rowspan) 
                    {
                        // rowspanが存在する場合は数値として+1する
                        $(this).attr('rowspan', parseInt(rowspan, 10) + 1);
                    }
                });

                // blade側で作成されている tr の data-state を読み取る
                var complateState = ($(firstTr).data('state'));

                // noneはrowspanで被ったときに、ズレるのを防ぐため
                var newTr = $('<tr name="'+ sheetName +'" id="tr-' + trCounter + '" data-state="'+ complateState +'"><td class="td_item invisibleCell"><td class="td_item invisibleCell"></td><td class="td_item invisibleCell"></td><td class="td_item invisibleCell"></td><td class="td_item invisibleCell"></td><td class="td_item invisibleCell"></td><td class="td_item invisibleCell"></td><td class="td_item fixedCell"></td></tr>');
                trCounter++;
                newTr.insertAfter(lastTr);
                tr = newTr;
            };
 
            // trの数ぶん足りない、空のtdを追加する
            $("tbody tr").each(function() 
            {
                var tr = $(this);
                while (tr.find('td').length < fullDates.length + 8) 
                {
                    tr.append('<td class="moveCell blank"></td>');
                }
            });

            // データ挿入（セル内に表示する）
            for (var i = planIdx + 8; i <= lastDayIdx + 8; i++) 
            {
                var $cell = tr.find('td').eq(i);
                var $div = $('<div class="bodyArrow"></div>');
                // console.log("plan, No. : " + plan["No."]);
                
                var circleNum = plan["No."];

                $cell.attr('name', plan["項目"] + " / " + plan["作業詳細"] + " / " + (plan["担当者"] && plan["担当者"].trim() ? plan["担当者"] : "担当者なし"));
                $cell.addClass('arrows');

                // 完了日が入力されていたら、矢印の背景色自体変える
                if (complateIdx != -1)
                {
                    $cell.addClass('complate');
                }
                
                // 矢印追加（生成）
                $cell.append($div);

                // 遅れている場合
                if (isLate)
                {
                    // 矢印の種類の判定
                    if (i == planIdx + 8)               // 予定日
                    {
                        $div.addClass('plan');
                        $div.text(circleNum);
                    }
                    else if (i == startIdx + 8)         // 着手日
                    {   
                        $div.addClass('start');
                    }
                    else if (i >= deadIdx + 8 && i < lastDayIdx + 8) // 納期超えてる
                    {
                        $div.addClass('late');
                    }
                    else if (i == lastDayIdx + 8)       // 矢印の最終日
                    {
                        $div.addClass('headArrow');
                        var  $arrowDiv = $('<div class="outerArrow"><div class="innerArrow late"></div></div>');
                        $div.append($arrowDiv);
                    }
                    else
                    {
                        $div.addClass('safe');
                    }
                }
                else // 期限通りの場合
                {
                    // 矢印の種類の判定
                    if (i == planIdx + 8)               // 予定日
                    {
                        $div.addClass('plan');
                        $div.text(circleNum);
                    }
                    else if (i == startIdx + 8)         // 着手日
                    {   
                        $div.addClass('start');
                    }
                    else if (i == deadIdx + 8)          // 納期
                    {
                        // 矢印
                        $div.addClass('headArrow');
                        var  $arrowDiv = $('<div class="outerArrow"><div class="innerArrow safe"></div></div>');
                        $div.append($arrowDiv);
                    }
                    else if (i == complateIdx + 8)      // 完了日
                    {   
                        $div.addClass('complate');
                    }
                    else                                // 矢印中の何もない日
                    {
                        $div.addClass('safe');
                    }
                }
            }    
        });
    }
         

    // 今日の日付が無い場合の処理（遅延矢印更新の為）====================================================================================
    function notToday(today,end)
    {
        var todayObj = new Date(today);
        var endDateObj = new Date(end);

        // 今日の日付があるかないか
        if (todayObj > endDateObj)
        {
            notTodayState = true;
        }
        else
        {
            notTodayState = false;
        }
    }

    // YYYY-MM-DD 形式のチェック関数 ==================================================================================================
    function isValidDateFormat(dateStr) 
    {
        return /^\d{4}-\d{2}-\d{2}$/.test(dateStr);
    }

    // 開始日から終了日までの間の日数を全て求める（th作成の為） ===========================================================================
    function getDatesInRange(start, end) 
    {
        let startDate = new Date(start);
        let endDate = new Date(end);
        let dateArray = [];

        while (startDate <= endDate) 
        {
            dateArray.push(formatDate(startDate));
            startDate.setDate(startDate.getDate() + 1);
        }
        // console.log(dateArray);
        return dateArray;
    };

    // 日付の形式変更 ===================================================================================================================
    function formatDate(date) 
    {
        // 比較用
        let year = date.getFullYear();
        let fullMonth = (date.getMonth() + 1).toString().padStart(2, '0'); // 月を2桁にする
        let fullDay = date.getDate().toString().padStart(2, '0'); // 日を2桁にする

        // 表示用
        let month = date.getMonth() + 1; // 月は0から始まるため1を足す
        let day = date.getDate();

        return {
            // fullDate: `${year}/${month}/${day}`, 
            fullDate: `${year}-${fullMonth}-${fullDay}`, // 比較のための形式を合わせるため
            shortDate: `${month}/${day}`
        };
    };

    // 配列内での現在の場所取得（YYYY-MM-DD = 〇番目） ====================================================================================
    function getDateIndex(fullDate, date) 
    {
        return fullDate.indexOf(date);
    };
    
    // // 丸数字へ再度変換（blade側で変換処理している） ====================================================================================
    // function toCircled(num) 
    // {
    //     if (num <= 20) 
    //     {
    //         const base = '①'.charCodeAt(0);
    //         return String.fromCharCode(base + num - 1);
    //     }
    //     if (num <= 35) 
    //     {
    //         const base = '㉑'.charCodeAt(0);
    //         return String.fromCharCode(base + num - 21);
    //     }

    //     const base = '㊱'.charCodeAt(0);

    //     return String.fromCharCode(base + num - 36);
    // }

    // ツールチップ（吹き出し）の位置判定 =====================================================================
    
    // マウスカーソルが矢印の上に乗った時の処理
    function hover()
    {
        // マウスが乗ったら
        $(".arrows").on('mouseover',function()
        {
            
            var arrowData =  $(this).attr('name').replace(/ \/ /g, '<br>');
       
            // 吹き出しの span を作成
            var $tooltip = $('<span class="tooltips">' + arrowData + '</span>');
            $(this).append($tooltip);

            // ツールチップの位置調整
            let $cell = $(this);
            let tooltipWidth = $tooltip.outerWidth();
            let tooltipHeight = $tooltip.outerHeight();
            let cellWidth = $cell.outerWidth();
            let cellOffset = $cell.offset();
            // let scrollBoxOffset = $('.scrollBox').offset();
            let scrollBoxWidth = $('.scrollBox').outerWidth();
            let scrollBoxHeight = $('.scrollBox').outerHeight();

            // ツールチップのある位置
            let top = cellOffset.top - tooltipHeight / 2;
            let left = cellOffset.left + cellWidth;

            console.log("tooltipWidth : " + tooltipWidth + " / tooltipHeight : " + tooltipHeight);
            console.log("scrollBoxWidth : "  + scrollBoxWidth + "scrollBoxHeight : " + scrollBoxHeight);
            console.log("top : " + top + " / left : " + left);
            
            // ツールチップが右下にはみ出す場合（ツールチップのある幅 + ツールチップの横幅 > table全体の横幅 && ツールチップのある高さ + ツールチップの大きさ > table全体の大きさ）
            if (left + tooltipWidth > + scrollBoxWidth && top > scrollBoxHeight)
            {
                // console.log("右下にはみ出る");
                $tooltip.addClass('overAll');
            }

            // ツールチップが右にはみ出す場合（ツールチップのある幅 + ツールチップの横幅 > table全体の横幅）
            else if (left + tooltipWidth > + scrollBoxWidth) 
            {
                // console.log("右にはみ出る");
                $tooltip.addClass('overRight');
            }
            // ツールチップが下にはみ出す場合（ツールチップのある高さ + ツールチップの大きさ > table全体の大きさ）
            else if (top > scrollBoxHeight)
            {
                // console.log("下にはみ出る");
                $tooltip.addClass('overBottom');
            }
            else if (190 > top)
            {
                // console.log("下にはみ出る");
                $tooltip.addClass('nomal');
            }
            else
            {
                $tooltip.addClass('nomal');
            }
        });
    
        // マウスアウト時にツールチップを非表示（消している）にする処理
        $('.arrows').on('mouseout', function() 
        {
            $(this).find('.tooltips').remove();
        });
    }
});

// 前の画面に戻る（遷移後にリロード）
function goBackAndReload() 
{
    setTimeout(function() 
    {
        location.reload(); // 1秒後にページをリロードする
    }, 5); // 1000ミリ秒 (1秒) の遅延
}

// モーダル要素を取得
var modal = document.getElementById("myModal");
// モーダルを開くボタンを取得
var btn = document.getElementById("updateBtn");
// モーダルを閉じるアイコン（×）を取得
var span = document.getElementById("closeModal");

// ボタンがクリックされた時にモーダルを表示
btn.onclick = function() {
    modal.style.display = "block"; // モーダルのdisplayスタイルを"block"にして表示
}

// ×（クローズアイコン）がクリックされた時にモーダルを非表示
// span.onclick = function() {
//         modal.style.display = "none"; // モーダルのdisplayスタイルを"none"にして非表示
//     }
// }