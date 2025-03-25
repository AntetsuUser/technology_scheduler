<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}

    <title>Laravel WebSocket</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- <script type="module" src="{{ asset('js/app.js') }}"></script> --}}
    {{-- <script type="module" src="{{ asset('js/bootstrap.js') }}"></script> --}}

</head>
<body>
    <div class="is_update" id="is_update">
        
        {{-- {{ session()->put('updating', false); }} --}}
        {{-- {{ $hoge = session()->get('updating'); }}
        {{ dd($hoge); }} --}}
        {{-- @foreach ($errors->all() as $error)
            {{ $error }}
        @endforeach --}}
    </div>
    {{-- {{ public_path('ajax/update_management.php'); }} --}}

    <form method="POST" action="{{ route('update.process') }}" id="myForm">
    {{-- <form method="POST" action="{{ route('update.auto') }}" id="myForm"> --}}
        @csrf

        <div>
            <button id="process_Button">工程</button>
            {{-- <button id="auto_Button">自動化</button>
            <button id="list_Button">一覧</button> --}}
        </div>
    </form>

    {{-- <form method="POST" action="{{ route('update.process') }}" id="myForm"> --}}
    <form method="POST" action="{{ route('update.auto') }}" id="myForm">
        @csrf

        <div>
            <button id="process_Button">自動</button>
            {{-- <button id="auto_Button">自動化</button>
            <button id="list_Button">一覧</button> --}}
        </div>
    </form>

    <button id="test">test</button>

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

    <script src="{{ asset('js/ws.js') }}"></script>
</body>
</html>
