<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
    </head>
    <body style="width: 100%; height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div style="display: flex; flex-direction: column; gap: 24px">
            @php
                $sessionToken = explode('?session_token=', app('request')->get('redirect_uri'));
            @endphp

            <form action="{{ app('request')->get('redirect_uri') }}">
                <input type="hidden" name="session_token" value="{{ last($sessionToken) }}">
                <button style="padding: 50px; font-size: 24px; cursor: pointer;" type="submit">Connect</button>
            </form>
        </div>
    </body>
</html>
