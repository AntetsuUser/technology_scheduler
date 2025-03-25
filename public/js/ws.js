$(document).ready(function() 
{
    let url = '../ajax/update_management.php';
    ajax(url);

    // $('#myForm').on('submit', function(event)
    // {
    //     event.preventDefault(); // フォームのデフォルトの送信を防ぐ

    //     $('#is_update_process').append('<p>更新中です。しばらくお待ちください。</p>')
    //     $("#process_Button").prop("disabled", true);

    //     $.ajax({
    //         url: '../ajax/update_management.php',
    //         type: "POST",
    //         dataType: "json",
    //         data: 
    //         {
    //             id: 1,
    //         },
    //     })
    //     .done(function (json) 
    //     {
    //         console.log(json[0]['process_update']);
    
    //         let curr_process = json[0]['process_update'];
    //         if (curr_process == 1)
    //         {
    //             $('#is_update_process').append('<p>更新中です。しばらくお待ちください。</p>')

    //             console.log("return")
    //             return true;
    //             // window.location.href = "/ws"
    //         }
    //     })
    //     .fail(function (jqXHR, textStatus, errorThrown) 
    //     {
    //         window.alert("システム担当にご連絡ください。");
    //         console.log("Ajax,失敗");
    //         console.log("jqXHR : " + jqXHR);
    //         console.log("textStatus : " + textStatus);
    //         console.log("errorThrown : " + errorThrown);
    //     });

    //     console.log("----------------------------------------------");

    //     $.ajaxSetup(
    //     {
    //         headers: 
    //         {
    //           "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    //         },
    //     });

    //     $.ajax(
    //     {
    //         type: "POST",
    //         url: "/update/process",
    //     })
    //     .done(function (json) 
    //     {
    //         console.log(json);

    //         // window.location.href = "/ws"
    //     })
    //     .fail(function (jqXHR, textStatus, errorThrown) 
    //     {
    //         window.alert("システム担当にご連絡ください。");
    //         console.log("Ajax,失敗");
    //         console.log("jqXHR : " + jqXHR);
    //         console.log("textStatus : " + textStatus);
    //         console.log("errorThrown : " + errorThrown);
    //     });
    // });

    $('#process_Button').on('click', function() 
    {
        $.when(
            $('.is_update_process').append('<p>process 更新中です。しばらくお待ちください。</p>')
        ).done(function()
        { 
            ajax(url);
        });
    });

    $('#auto_Button').on('click', function() 
    {
        $.when(
            $('.is_update_auto').append('<p>auto 更新中です。しばらくお待ちください。</p>')
        ).done(function()
        { 
            ajax(url);
        });
    });
});

function ajax(url)
{
    $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        data: 
        {
            id: 1,
        },
    })
    .done(function (json) 
    {
        // 師匠に要相談
        console.log(json[0]['process_update']);
        
        let curr_process = json[0]['process_update'];

        if (curr_process == 1)
        {
            $('#is_update_process').append('<p>更新中です。しばらくお待ちください。</p>')
            $("#process_Button").prop("disabled", true);
        }
    })
    .fail(function (jqXHR, textStatus, errorThrown) 
    {
        window.alert("システム担当にご連絡ください。");
        console.log("Ajax,失敗");
        console.log("jqXHR : " + jqXHR);
        console.log("textStatus : " + textStatus);
        console.log("errorThrown : " + errorThrown);
    });
}

function getCSRFToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

