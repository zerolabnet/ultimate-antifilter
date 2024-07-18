<?php
// Простая форма авторизации
//-----------------------------------
session_start();

require 'pwd.php';

// MD5 хэш от хэша пароля с солью для проверки при входе
$hash = md5($salt1.$password.$salt2);

$self = $_SERVER['REQUEST_URI'];

// Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['loggedIn_cal']);
    header('Location: '.$_SERVER['HTTP_REFERER']);
}

// Если мы пришли сюда из формы
if (isset($_POST['login'])) {
    // Проверка пароля
    if (password_verify($_POST['password'], $password)) {
        $_SESSION['loggedIn_cal'] = $hash;
    } else {
        echo '<script>alert("Wrong password!")</script>';
    }
}

// Если сессия не была получена и хэш не совпадает, то показываем форму входа
if (!isset($_SESSION['loggedIn_cal']) || $_SESSION['loggedIn_cal'] != $hash) {
    die("
        <html>
        <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <title>Der Parol</title>
        <link rel='stylesheet' href='assets/normalize.min.css'>
        <style>
        *, *:before, *:after {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: sans-serif;
        }

        .container {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        .container:hover .top:before, .container:hover .top:after, .container:hover .bottom:before, .container:hover .bottom:after, .container:active .top:before, .container:active .top:after, .container:active .bottom:before, .container:active .bottom:after {
            margin-left: 200px;
            transform-origin: -200px 50%;
            transition-delay: 0s;
        }
        .container:hover .center, .container:active .center {
            opacity: 1;
            transition-delay: 0.2s;
        }

        .top:before, .top:after, .bottom:before, .bottom:after {
            content: '';
            display: block;
            position: absolute;
            width: 200vmax;
            height: 200vmax;
            top: 50%;
            left: 50%;
            margin-top: -100vmax;
            transform-origin: 0 50%;
            transition: all 0.5s cubic-bezier(0.445, 0.05, 0, 1);
            z-index: 10;
            opacity: 0.65;
            transition-delay: 0.2s;
        }

        .top:before {
            transform: rotate(45deg);
            background: #e46569;
        }
        .top:after {
            transform: rotate(135deg);
            background: #ecaf81;
        }

        .bottom:before {
            transform: rotate(-45deg);
            background: #60b8d4;
        }
        .bottom:after {
            transform: rotate(-135deg);
            background: #3745b5;
        }

        .center {
            position: absolute;
            width: 400px;
            height: 400px;
            top: 50%;
            left: 50%;
            margin-left: -200px;
            margin-top: -200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px;
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.445, 0.05, 0, 1);
            transition-delay: 0s;
            color: #333;
        }
        .center input {
            width: 100%;
            padding: 15px;
            margin: 5px;
            border-radius: 1px;
            border: 1px solid #ccc;
            font-family: inherit;
        }
        </style>
        </head>

        <body ontouchstart=''>
        <form class='container' action = '$self' method = 'POST'>
            <div class='top'></div>
            <div class='bottom'></div>
            <div class='center'>
                <input type='password' name='password' placeholder='Der Parol' autofocus />
                <input type='submit' name='login' hidden />
            </div>
        </form>
        </body>
        </html>
    ");
}
//-----------------------------------
// Простая форма авторизации

// Конвертируем в формат Shadowrocket
function convert_to_shadowrocket($fromFile, $toFile) {
    $result = file_get_contents($fromFile);
    if ($result != "\0") {
        // Разбиваем содержимое на строки
        $lines = explode(PHP_EOL, $result);
        // Определяем префикс в зависимости от имени файла
        if ($_POST['fileName'] == "proxy-domain" || $_POST['fileName'] == "direct-domain") {
            $prefix = 'DOMAIN-SUFFIX,';
        } elseif ($_POST['fileName'] == "proxy-ip" || $_POST['fileName'] == "direct-ip") {
            $prefix = 'IP-CIDR,';
        } else {
            $prefix = '';
        }
        // Добавляем соответствующий префикс в начало каждой непустой строки
        foreach ($lines as &$line) {
            if (!empty(trim($line))) { // Игнорируем пустые строки
                if (strpos($line, 'domain:') !== false && $prefix == 'DOMAIN-SUFFIX,') {
                    $line = str_replace('domain:', 'DOMAIN-SUFFIX,', $line);
                } elseif (strpos($line, 'keyword:') !== false && $prefix == 'DOMAIN-SUFFIX,') {
                    $line = str_replace('keyword:', 'DOMAIN-KEYWORD,', $line);
                } elseif (strpos($line, 'full:') !== false && $prefix == 'DOMAIN-SUFFIX,') {
                    $line = str_replace('full:', 'DOMAIN,', $line);
                } else {
                    $line = $prefix . $line;
                }
            }
        }
        // Объединяем строки обратно в одно содержимое
        $result = implode(PHP_EOL, $lines);
    }
    file_put_contents($toFile, $result);
}

// Конвертируем в формат Clash из формата Shadowrocket
function convert_to_clash($fromFile, $toFile) {
    // Добавляем .list к имени файла, так как конвертируем из формата Shadowrocket, а не из исходного файла
    $fromFile .= '.list';
    $result = file_get_contents($fromFile);
    if ($result != "\0") {
        $result = preg_replace('/^(.*)$/m', '  - $1', $result);
        $result = "payload:\n" . $result;
    }
    file_put_contents($toFile, $result);
}

// Загружаем файл -- Вызываем через AJAX для заполнения текстовой области
if (isset($_POST['retrieve']) && $_POST['retrieve'] == "1") {
    if (isset($_POST['fileName'])) {
        die(file_get_contents($_POST['fileName']));
    }
}

// Сохраняем файл -- Вызываем через AJAX функцию doSave()
else if (isset($_POST['save']) && $_POST['save'] == "1") {
    // Удаляем пробелы или другие символы из начала и конца строки
    $content = trim($_POST['content']);
    // Если есть содержимое, то добавляем перевод строки в конце файла
    if ($content != "") {
        $content = $content . PHP_EOL;
    } else { // иначе – fwrite не любит записывать пустые файлы, поэтому мы превращаем пустую строку в экранированную пустую строку
        $content = "\0";
    }

    // Массив для сопоставления имен файлов с действиями
    $fileActions = [
        "proxy-domain" => ["convert_to_shadowrocket" => "proxy-domain.list", "convert_to_clash" => "proxy-domain.yaml", "script" => "/srv/geosite.sh"],
        "direct-domain" => ["convert_to_shadowrocket" => "direct-domain.list", "convert_to_clash" => "direct-domain.yaml", "script" => "/srv/geosite.sh"],
        "proxy-ip" => ["convert_to_shadowrocket" => "proxy-ip.list", "convert_to_clash" => "proxy-ip.yaml", "script" => "/srv/geoip.sh"],
        "direct-ip" => ["convert_to_shadowrocket" => "direct-ip.list", "convert_to_clash" => "direct-ip.yaml", "script" => "/srv/geoip.sh"],
    ];

    // Если мы смогли открыть файл
    if ($file = fopen($_POST['fileName'], "w")) {
        // Записываем его
        if (fwrite($file, $content)) {
            fclose($file);
            // Проверяем, есть ли действия для данного имени файла
            if (isset($fileActions[$_POST['fileName']])) {
                $actions = $fileActions[$_POST['fileName']];
                convert_to_shadowrocket($_POST['fileName'], $actions['convert_to_shadowrocket']);
                convert_to_clash($_POST['fileName'], $actions['convert_to_clash']);
                shell_exec($actions['script']);
            }
            die(json_encode(["desc" => "Saved", "level" => "success"]));
        } else {
            die(json_encode(["desc" => "An error occurred writing the file", "level" => "fatal"]));
        }
    } else {
        die(json_encode(["desc" => "Could not open file", "level" => "fatal"]));
    }
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAQAAAD9CzEMAAADTklEQVR42t3XTWxUVRQH8N+0FDAYIbEVAwQWjdZEgVDtgmoUFm4kMboQGyTKQhKNLggUE6NR2JBYhcaASV3RsBBkJSQsFAPE0CBoXPARMNAvbFoESmmnX7QzvS54mUCYeZ2avg33v3nv5v7P/757zrnnPBqMCglhVIMEzd+VSNR8EB4UyOiXlo2hTEjnvnvcgInoecTwZAJZZ33hbe/YobWgwBkbfOam4I5mdfYLgl5bbdQeL3Dec0rBDDXO5TU/7lMlyv0s6FGL5QYFJ5WhIU4gYxUoN09KyvuG8koc9LSXtQsGbbbYFkFwVa1KZ+IEzkthuV8ctABVOvMKZFx2I3pOu2Q88kyPrngfHAGfCLJq8bjL0xtFF83AYi1+VIFnXY0hprW6Ge39urYorsZ16i4kMOGtyMFlmGFz3rC7izu+tsBavYLr3rBIkyA4ocoL9x3tfbQ2q6TcHa/6J3b/67HEFUGbRVgnCL4HJwsn2i27valOs8wkZ9tireboOmiyLsqbGz62PZd6eTM58ati2gWGEjU/RL2jjieEo+opU64iIZQrk/yotN6GhLBeJaekDSaEtFMPQx48OHXbwCSk21ENCDL6crPDeTuU+17GHPY8yqzWYqxAyT9orhU6BEM+UqpeVtBhqXn+jBf4wRO56HrSoby9RdaXSlQ4JvjXK6g2KvjDTOyOExixECzzjJSUNXrzfsPfPvCNIUHGIe86IQgG7LDFrTiB38BrJlxThYUxrcv/cvKv4D0T+q3AfFemV6DXI5ijzutm4UXXYojtGqImJeu4RmlBMKjZkcJO3mZWzslzfBtT1YZtRKUuQaen8KEg2C8l5WwhgT6fW4JSVb5yO2b/I7YqU6tH0K1Gqe2C4JDHzHexcKKNaNfid50FsuDeAz3pqglBRqszUXKO+sulh60mdydqvpuVmuwtEqdlZZ0uen2TlVOrfpuMGbMpufKasMBs24wbt83sJMzPtVOPILhml7nTbT5lT66KBRl7cl34pKPWTt9Nir36BX0aNeoT9NtbBGuXl+iYQlTvM8ej9k2B0cnhKSw/piIqlsXiJ2ZZpmZSrHZBEHRFf5IXrC6CtdTM4t285p6f1C5rpj9MS1Q7YMCAA6qVFEv7D4KwRiCyg8MQAAAAAElFTkSuQmCC" />
    <title>Ultimate antifilter</title>
    <script src="assets/jquery-2.2.0.min.js"></script>
    <script src="assets/taboverride.min.js"></script>
    <style>
        a:link,
        a:visited {
            color: #555555;
            text-decoration: none;
            background: none;
        }

        a:hover {
            text-decoration: underline;
            background: none;
        }

        body {
            font-family: Arial, sans-serif;
            background: #fafafa;
            color: #bcbdbc;
        }

        .topnav {
            overflow: hidden;
            background-color: #f3f4f7;
            border: 1px solid #e9e9e9;
            margin: 0em 0em .35em 0em;
        }

        .topnav a {
            float: left;
            display: block;
            color: #404040;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            font-size: 17px;
        }

        .topnav a:hover {
            background-color: #ddd;
            color: black;
        }

        .topnav .left-container {
            float: left;
        }

        .topnav .left-container button {
            background: #3cb371;
            border: none;
            cursor: pointer;
            color: #fff;
            font-family: Arial;
            padding: 10px 70px;
            margin: 4px;
            text-decoration: none;
            font-size: 17px;
        }

        .topnav .left-container button:hover {
            background: #0066cc;
        }

        .topnav .right-container {
            float: right;
            background: #ccc;
        }

        .topnav .right-container a:hover {
            background: #0066cc;
            color: #fff;
        }

        .bottomnav {
            margin: .35em 0em 0em 0em;
        }

        .bottomnav div {
            margin-bottom: .35em;
        }

        .bottomnav .copyright {
            text-align: center;
            font-size: 14px;
        }

        .tab {
            cursor: pointer;
            padding: 10px 20px;
            display: inline-block;
            margin-right: 5px;
            border: 1px solid #e9e9e9;
            background-color: #f3f4f7;
        }
        .tab.active {
            background-color: #ffffff;
        }

        img.icon-clash {
            content: url(data:image/png;base64,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);
            border: none;
            width: 16px;
            height: 16px;
        }

        img.icon-shadowrocket {
            content: url(data:image/png;base64,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);
            border: none;
            width: 16px;
            height: 16px;
        }

        img.icon-v2ray {
            content:  url( data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAQAAADZc7J/AAABpElEQVR42sWPzSuEQRzHvzP7zDP7zDM8rV22lhyWFE7aHBxc8A9I6yIXB1mrpCgHxdHNgeQqxcWVgws5cKZcSBy8F7KK8rJL0r7Ms/Q0Dj7f2/zm+/nN4M+U7IsLcW5l4z8btVGEKSrb7AP7SlyJy69cfOYkPAzfDTKFGZRwQ0UfvVdv8vmw7U3AZDdJKfde7cW4D4CcsKftab7xs6CWmxP0SV3jXw44yCGSuVGvXVi3xsmrUn9nWzGBfHgiN+7PGwUcPkfe1O3GWmVQ/eRAbjxiZY/9cgFppZ5mR7EQ8Isg/i0IRcxVZNSw7WAl4EHQYvFN4qr77ipqUAyWKPxCsJ7tuLcbe9E6uHG/IOoYh+7t5DHSBHgQhDvYUZHHHwdaAU8C3y3c2x+cdsCjwB16XdYFoi0gqZJOANAU0JRITlFtAXkWfaCApoC8+CdjDNAUkBdrrNEENAXknc1WWfAKSyiCNJ8pl4C2wFyKCEBbIFdkOaAtMNaVv3uBD2bru5FqQFvAduImdODJr/qBbIAe1hAyxmHUAbQF9LS0GfqEehqr8T98AADGbmitpnjPAAAAAElFTkSuQmCC);
            border: none;
            width: 16px;
            height: 16px;
        }

        img.fas-fa-terminal {
            content: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAAwCAQAAADnN9GCAAAA7ElEQVR42u3YPQrCQBAF4HcYL2Bq8QZiav//Fe08QLAQy72Rd9gTeAGrVCLE3TRBUdNkVuaBZKYOHwnJZuYBdSlXC1s09NkjPDxu6OiyJ/ii75r0/skq09c3WJG+fMBqdBcZi+7x6D4eLHrAo4c8esSjxzx6wqOncCx6xqPnPHrJo1c8el1KtzXoTQl9/mOY9KhJLxfpc1pwWNK5RTqpSb9F0iBAGn1Iwx5pvCUN9LIVJkaCg6gTxPKlzXxdU62NdE1NA+FUupj74BZGET+AZeGLC2SdNG6ygbB9BWy7SgFbBCu+aweLZp1pqlUORrl5l33U14cAAAAASUVORK5CYII=);
            border: none;
            width: 14px;
            height: 11px;
        }

        /* PC */
        @media screen and (min-width: 1000px) {
        #editor {
            resize: none;
            overflow-x: scroll;
            background: #fff;
            color: #383838;
            font-family: monospace;
            width: 100%;
            height: calc(100% - 12.5em);
            font-size: 13px;
            padding: 10px;
            tab-size: 2em;
            -moz-tab-size: 2em;
            -o-tab-size: 2em;
            white-space: pre;
        }
        }
        /* PC */

        /* Mobile */
        @media screen and (min-width: 480px) and (max-width: 1000px) {
        #editor {
            resize: none;
            overflow-x: scroll;
            background: #fff;
            color: #383838;
            font-family: Courier;
            width: 100%;
            height: calc(100% - 9.5em);
            font-size: 42px;
            padding: 10px;
            tab-size: 2em;
            -moz-tab-size: 2em;
            -o-tab-size: 2em;
            white-space: pre;
        }
        }
        /* Mobile */
    </style>
    <script>
        // Минимизированное сокращение AJAX — любезно предоставлено iworkforthem на Github
        function postAjax(url, data, success) {
            var params = typeof data == 'string' ? data : Object.keys(data).map(function(k){return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])}).join('&');
            var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
            xhr.open('POST', url);
            xhr.onreadystatechange = function() {
                if(xhr.readyState>3 && xhr.status==200) { success(xhr.responseText); }
            };
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send(params);
            return xhr;
        }
        // Переменные GET в javascript — любезно предоставлено gion_13 на StackOverflow
        var $_GET = {}; if(document.location.toString().indexOf('?') !== -1) { var query = document.location.toString().replace(/^.*?\?/, '').replace(/#.*$/, '').split('&'); for(var i=0, l=query.length; i<l; i++) { var aux = decodeURIComponent(query[i]).split('='); $_GET[aux[0]] = aux[1]; } }

        // Заполняем textarea
        function loadFile(file) {
            postAjax("?", {"retrieve": "1", "fileName": file}, function(data) {
                document.getElementById("editor").innerHTML = data;
            });
        }

        // Имя и путь до файла по умолчанию (вкладка Proxy)
        var fileName = "proxy-domain";

        function setupTabHandlers() {
            loadFile(fileName); // Load the default file
            $('#tab-proxy-domain').click(function() {
                fileName = "proxy-domain";
                loadFile(fileName);
                $('#tab-proxy-domain').addClass('active');
                $('#tab-direct-domain').removeClass('active');
                $('#tab-proxy-ip').removeClass('active');
                $('#tab-direct-ip').removeClass('active');
            });

            $('#tab-direct-domain').click(function() {
                fileName = "direct-domain";
                loadFile(fileName);
                $('#tab-proxy-domain').removeClass('active');
                $('#tab-direct-domain').addClass('active');
                $('#tab-proxy-ip').removeClass('active');
                $('#tab-direct-ip').removeClass('active');
            });

            $('#tab-proxy-ip').click(function() {
                fileName = "proxy-ip";
                loadFile(fileName);
                $('#tab-proxy-domain').removeClass('active');
                $('#tab-direct-domain').removeClass('active');
                $('#tab-proxy-ip').addClass('active');
                $('#tab-direct-ip').removeClass('active');
            });

            $('#tab-direct-ip').click(function() {
                fileName = "direct-ip";
                loadFile(fileName);
                $('#tab-proxy-domain').removeClass('active');
                $('#tab-direct-domain').removeClass('active');
                $('#tab-proxy-ip').removeClass('active');
                $('#tab-direct-ip').addClass('active');
            });
        }

        // Сохранить файл
        function doSave() {
            postAjax("?",{"save":"1","fileName":fileName,"content":document.getElementById('editor').value},function(data) {
                try { data = JSON.parse(data); }
                catch(e) { console.log(data); data = {desc:'An unknown error occurred'}; }
                    document.getElementById('saveStatus').innerHTML = '::. ' + data.desc + ' .::';
                    window.setTimeout(function() {
                        $('#saveStatus').fadeOut(
                            function() {
                                document.getElementById('saveStatus').innerHTML = 'Apply rules to the current tab';
                                document.getElementById('saveStatus').style['display']='inline';
                            }
                        );
                    // После успешного сохранения данных перенаправляем на ту же страницу
                    window.location.href = window.location.href;
                    },3000);
            });
        }

        // Ctrl+S вызывает функцию doSave()
        $(document).keydown(function(e) {
            if((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey)){
                e.preventDefault();
                doSave();
            }
        });

        $(document).ready(function() {
            setupTabHandlers();
        });
    </script>
</head>
<body>
    <div class="topnav">
        <div class="left-container">
            <button id='saveStatus' onclick='doSave();'>Apply rules to the current tab</button>
            <span id="tab-proxy-domain" class="tab active">Proxy domain/keyword/full</span>
            <span id="tab-direct-domain" class="tab">Direct domain/keyword/full</span>
            <span id="tab-proxy-ip" class="tab">Proxy IP-CIDR</span>
            <span id="tab-direct-ip" class="tab">Direct IP-CIDR</span>
        </div>
        <div class="right-container">
            <a href="?logout">Logout</a>
        </div>
    </div>

    <!-- Текстовое поле, где происходит все редактирование. Заполняется через AJAX. -->
    <textarea name='newContent' id='editor'></textarea>

    <div class="bottomnav">
        <div><img class="icon-clash"> Clash lists:
            <a href="antifilter-ip.yaml">Antifilter IP-CIDR</a> |
            <a href="antifilter-community-ip.yaml">Antifilter community IP-CIDR</a> |
            <a href="antifilter-community-domain.yaml">Antifilter community domain</a> |
            <a href="proxy-domain.yaml">Proxy domain/keyword/full</a> |
            <a href="direct-domain.yaml">Direct domain/keyword/full</a> |
            <a href="proxy-ip.yaml">Proxy IP-CIDR</a> |
            <a href="direct-ip.yaml">Direct IP-CIDR</a>
        </div>
        <div><img class="icon-shadowrocket"> Shadowrocket lists:
            <a href="antifilter-ip.list">Antifilter IP-CIDR</a> |
            <a href="antifilter-community-ip.list">Antifilter community IP-CIDR</a> |
            <a href="antifilter-community-domain.list">Antifilter community domain</a> |
            <a href="proxy-domain.list">Proxy domain/keyword/full</a> |
            <a href="direct-domain.list">Direct domain/keyword/full</a> |
            <a href="proxy-ip.list">Proxy IP-CIDR</a> |
            <a href="direct-ip.list">Direct IP-CIDR</a>
        </div>
        <div><img class="icon-v2ray"> v2rayGeoIPDat DBs:
            <a href="geoip.dat">IP-CIDR (antifilter + antifilter-community + proxy & direct lists provided above)</a> |
            <a href="geosite.dat">Domain/keyword/full (antifilter-community + proxy & direct domain/keyword/full lists provided above)</a>
        </div>
        <div class="copyright"><img class="fas-fa-terminal"> created by ZeroChaos. Visit site: <a href="https://zerolab.net" target="_blank">zerolab.net</a></div>
    </div>

    <!-- Включаем табуляцию в текстовой области -->
    <script>tabOverride.set(document.getElementById('editor'));</script>
</body>
</html>
