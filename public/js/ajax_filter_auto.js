// 使いまわしやすいように
let file_type = '';             // 表示形式のselectボックスのvalue格納
let display_type = '';          // 表示形式のselectボックスのvalue格納
let worker_name = '';           // 表示形式のselectボックスのvalue格納
let colSpan = '';

// 起動されたとき
$(document).ready(function() 
{
    // 担当者のselectが変更されたとき
    $('#worker_name').change(function() 
    {   
        colSpan = $('th').length;

        // 選択されたら最初の要素のテキストを初期化（）
        $('#worker_name :first-child').text('指定なし');
        // $('#display_type :first-child').val('');
    
        // selected 属性を削除
        $('#worker_name option').removeAttr('selected');
        $('#display_type option').removeAttr('selected');  
        

        $('.noneData').remove();
        $('.error').remove();
        // 一旦表示を全部消す（並び替えの為） 
        $('.count-rowid tr').hide();            // hideは displaynone; が自動的に追加される

        $('.tableBase').append(`
            <div class="noneData alert alert-danger">
                <p class="text-center">対応するデータがありません。</p>
            </div>
        `);
        
        // 選択されたoptionの値を取得
        display_type = $('#display_type').val();
        worker_name = $(this).val();
        
        // 確認のため
        // console.log('選択した担当者：' + worker_name);
        // console.log('選択されている表示形式：' + display_type);
        
        workerSerch(worker_name);
    });

    // 表示形式のselectが変更されたとき
    $('#display_type').change(function() 
    {       
        colSpan = $('th').length;

        // 一旦表示を消す（並び替えの為）
        // display:none; -> htmlでのstyle =
        $('.noneData').remove();
        $('.error').remove();
        // 一旦表示を全部消す（並び替えの為） 
        $('.count-rowid tr').hide();            // hideは displaynone; が自動的に追加される

        $('.tableBase').append(`
            <div class="noneData alert alert-danger">
                <p class="text-center">対応するデータがありません。</p>
            </div>
        `);
     
        display_type = $(this).val();
        worker_name = $('#worker_name').val();

        // 確認のため
        // console.log('選択されている表示形式：' + worker_name);
        // console.log('選択した表示形式：' + display_type);

        workerSerch(worker_name);
    });

    // 削除のチェック
    // 削除ボタンがクリックされたとき
    // 削除ボタンがクリックされたとき
    // $('#deleteButton').on('click', function() {
    //     // チェックされたチェックボックスを探す
    //     $('#scheduleTable tbody input.delete-checkbox:checked').each(function() {
    //         // 親の <tr> 要素を削除
    //         $(this).closest('tr').remove();
    //     });
    // });
});

// ajaxを飛ばしてデータを取得 =============================================================================
function ajax(worker_name)
{
    $.ajax({
        url: 'http://192.168.3.96:8001/ajax/ajax_filter_auto.php', 
        type: 'POST',
        dataType: 'json',
        data: 
        {
            worker_name: worker_name        // 調べたい担当者の名前
        },
    })
    .done(function (data) 
    {
        // 取得した状態のデータ
        console.log('ajax : '); console.log(data);
        console.log("worker_name : " + worker_name);

        // 絞り込みしたデータ（ファイル名-シート名）
        // 複数ある場合があるので配列になっている
        let filterTableDatas = filterTable(data);

        // そもそものデータが無い場合
        if (!filterTableDatas || filterTableDatas.length === 0) 
        {
            console.log("対応するデータがありません。");
        }

        // 絞り込み =====================================================================================    
        filterTableDatas.forEach(tr_name => {
            $('.count-rowid tr').each(function() 
            {
                //  trのnameと絞り込んだ担当者が含まれる「ファイル名-シート名」が一緒だったら
                if ($(this).attr('name') == tr_name) 
                {
                    // 表示形式
                    if (display_type == "完了済")           // 「完了済」が選択されている
                    {
                        // tr の data-state が「完了」のものだけ
                        if ($(this).data('state') == "完了")
                        {
                            $(this).show();
                            $('.noneData').remove();
                        }
                    }
                    else if (display_type == "未完了")      // 「未完了」が選択されている
                    {
                        if ($(this).data('state') == "未完了")
                        {
                            $(this).show();
                            $('.noneData').remove();
                        }
                    }
                    else                                    // 「全体」が選択されている
                    {
                        $(this).show();
                        $('.noneData').remove();
                    }
                }

                
            });
        });

        console.log('=========================================================');
    })
    .fail(function (jqXHR, textStatus, errorThrown) //　エラーの場合
    {
        window.alert('DB接続に失敗しました。\nシステム担当にご連絡ください。');
        console.log('Ajax,失敗');
        console.log('jqXHR : ');
        console.log(jqXHR);
        console.log('textStatus : ' + textStatus);
        console.log('errorThrown : ' + errorThrown);
    });
}


// 担当者を探す ====================================================================================
function workerSerch(worker_name)
{
    console.log("担当者 : " + worker_name);

    if (worker_name != "")
    {
        ajax(worker_name);
       
    }
    else
    {
        $('.count-rowid tr').each(function() 
        {
            // 表示形式
            if (display_type == "完了済")           // 「完了済」が選択されている
            {
                // trのdata-stateが「完了」だったら
                if ($(this).data('state') == "完了")
                {
                    $(this).show();
                    $('.noneData').remove();
                }
            }
            else if (display_type == "未完了")      // 「未完了」が選択されている
            {
                if ($(this).data('state') == "未完了")
                {
                    $(this).show();
                    $('.noneData').remove();
                }
            }
            else                                    // 「全体」が選択されている
            {
                $(this).show();
                $('.noneData').remove();
            }

            
        });
    }
}

// trの表示、非表示 =====================================================================================
function filterTable(data) 
{
    // 送られてくる予定のデータの見本
    // [['aaa.xlsx-aaa', 'bbb.xlsx-bbb', 'ccc.xlsx-ccc'], ['完了', '完了', '未完了']]

    let tableName = '';

    // 最終的なデータを入れる
    let tableNames = [];

    // それぞれのデータを格納
    let excel_info = data['excel_info'];

    // 取得した個数分並び替え（3種とも同じ数なので、excel_infoで処理）
    for (let i = 0; i < excel_info.length; i++) 
    {
        excel_info[i].forEach(e_info => {
            let fileName = e_info['file_name'];
            let sheetName = e_info['sheet_name'];

            // // 完了フラグ（DB に書込されてれば取得できる）
            let complateState = e_info['complate_state'];

            tableName = fileName + '-' + sheetName;
            tableNames.push(tableName);

            console.log(tableName + ' / ' + complateState);
            console.log('表示形式 : ' + display_type);
        });
    }
    
    return tableNames;
}