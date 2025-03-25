<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>TEST</title>
    </head>
    <body>
        <div id="container"></div>
    </body>
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script src="/js/app.js"></script>
    <script>
        // {{-- チャンネルを監視 イベント検知すると処理実行 --}}
        window.Echo.channel('test-channel')
            .listen('WsRecvEvent', (e) => {
                $("#container").append("<div>" + e.comment + "</div>");
            });
    </script>
</html>
