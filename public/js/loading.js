// window.onload = function() 
// {
//     setTimeout(loadtime, 1500);
// }

$(function()
{
    // 画面がロードされたら（遷移後のイメージ）
    $(document).ready(function()    
    {
        setTimeout(loadtime, 1500);
    });

    function loadtime()
    {      
        const spinner = document.getElementById('loading');
        spinner.classList.add('loaded');
    };

    // ローディング画面を表示
    function showLoading() {
        $("#loading").show();  // ローディングを表示
    }

    // ローディング画面を非表示にする
    function hideLoading() {
        $("#loading").addClass('loaded'); // アニメーションを適用
    }

    // 更新ボタンがクリックされたら
    $('#updateBtn').click(function() 
    {
        // alert("さぁアップデートしよ？");
        // setTimeout(loadtime, 1500);

        showLoading(); // ボタンをクリックしたらローディングを表示
    });

});





