<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>系统发生错误</title>

    <style type="text/css">
        <?php
             $styles = [
                'css/prism.css'
             ];
             foreach ($styles as $style) {
               echo file_get_contents($data['resource_path'] .'/' . $style);
             }
        ?>
    </style>

    <style type="text/css">

        ::selection {
            background-color: #E13300;
            color: white;
        }

        ::-moz-selection {
            background-color: #E13300;
            color: white;
        }

        body {
            background-color: #fff;
            margin: 20px;
            font: 13px/20px normal Helvetica, Arial, sans-serif;
            color: #4F5155;
        }

        a {
            color: #868686;
            background-color: transparent;
            font-weight: normal;
            cursor: pointer;
        }

        h1 {
            color: #444;
            background-color: transparent;
            border-bottom: 1px solid #D0D0D0;
            font-size: 19px;
            font-weight: normal;
            margin: 0 0 14px 0;
            padding: 14px 15px 10px 15px;
        }

        .body {
            margin: 0 5px;
        }

        .body > p {
            margin-left: 10px;
        }

        .code {
            font-family: Consolas, Monaco, Courier New, Courier, monospace;
            font-size: 16px;
            background-color: #f9f9f9;
            border: 1px solid #D0D0D0;
            color: #868686;
            display: block;
            margin: 14px 0 14px 0;
            padding: 12px 10px 12px 10px;
        }

        .notice {
            font-size: 22px;
            font-weight: bold;
            margin: 15px 0;
            padding: 0;
        }

        p.footer {
            text-align: right;
            font-size: 11px;
            border-top: 1px solid #D0D0D0;
            line-height: 32px;
            padding: 0 10px 0 10px;
            margin: 20px 0 0 0;
        }

        .container {
            margin: 0;
            border: 1px solid #D0D0D0;
            box-shadow: 0 0 8px #D0D0D0;
        }

        .container h1 {
            font-size: 16px;
            color: #4288ce;
        }

        .error-line {
            background: red;
        }

        .err-msg {
            color: #E13333;
            font-weight: bold;
            font-size: 24px;
            margin: 15px 0;
            padding: 0;
            text-decoration: none;
        }

        .exception-name {
            font-weight: 600;
            font-size: 18px;
            cursor: pointer;
        }

        .line-numbers-rows {
            counter-reset: itemcounter <?=$data['source_code']['line']?> !important;
        }

        .line-numbers-rows > span {
            counter-increment: itemcounter;
        }

        .line-numbers-rows > span:before {
            content: counter(itemcounter) !important;
        }

        .line-highlight {
            background: red;
            opacity: 0.3;
        }

        .call-trace {
            margin: 0 0 0 30px;
            padding: 0;
        }

        .call-trace li {
            font-size: 14px;
        }

        .call-trace span {
            cursor: pointer;
            font-weight: bold;
        }

        .variables {
            font-size: 18px;
            font-weight: bold;
        }

        .variables-empty {
            font-size: 12px;
            color: rgba(0, 0, 0, .3);
            font-weight: 100;
        }

        .table {
            width: 100%;
            margin: 12px 0;
            box-sizing: border-box;
            table-layout: fixed;
            word-wrap: break-word;
        }

        .table caption {
            text-align: left;
            font-size: 16px;
            font-weight: bold;
            padding: 6px 0;
            margin-left: 10px;
        }

        .table small {
            font-weight: 300;
            display: inline-block;
            margin-left: 10px;
            color: #ccc;
        }

        .table td:first-child {
            width: 28%;
            font-weight: bold;
            white-space: nowrap;
        }

        .table td {
            padding: 0 10px;
            vertical-align: top;
            word-break: break-all;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>
        <?php
        echo "<span class='exception-name' title='" . $data['exception'] . "'>" . basename(str_replace('\\', DIRECTORY_SEPARATOR, $data['exception'])) . "</span>" . " in ";
        ?>
        <a class="toggle" href="javascript:;"
           title="<?= $data['file'] ?> line <?= $data['line'] ?>"> <?= basename($data['file']) ?>
            line <?= $data['line'] ?></a>
    </h1>

    <div class="body">

        <p>
            <a href="javascript:;" class="err-msg"><?= $data['message'] ?></a>
        </p>

        <pre data-line="<?= $data['source_code']['line'] ?>">
    <code class="language-php line-numbers">
        <?php foreach ($data['source_code']['source'] as $item): ?>
            <?= $item ?>
        <?php endforeach; ?>
    </code>
  </pre>
        <p class="notice">Call Stack</p>

        <ol class="call-trace">
            <li>in <span class="toggle" title="<?= $data['file'] ?>"> <?= basename($data['file']) ?></span> line
                <span><?= $data['line'] ?></span></li>
            <?php foreach ($data['traces'] as $trace): ?>
                <li>
                    at&nbsp;
                    <?php if (isset($trace['class'])): ?>
                        <span title="<?= $trace['class'] ?>"
                              class="toggle"><?= basename(str_replace('\\', DIRECTORY_SEPARATOR, $trace['class'])) ?></span>
                    <?php endif ?>
                    <?php if (isset($trace['type'])): ?>
                        <span><?= $trace['type'] ?></span>
                    <?php endif ?>
                    <?php if (isset($trace['function'])): ?>
                        <span><?= $trace['function'] ?></span>
                    <?php endif ?>
                    <?php if (isset($trace['file'])): ?>
                        in &nbsp;<span title="<?= $trace['file'] ?>"
                                       class="toggle"><?= basename($trace['file']) ?></span>
                    <?php endif ?>
                    <?php if (isset($trace['line'])): ?>
                        line &nbsp;<span><?= $trace['line'] ?></span>
                    <?php endif ?>
                </li>
            <?php endforeach; ?>
        </ol>
        <p class="notice">Environment & details</p>
        <div class="variables-list">
            <?php foreach ($data['tables'] as $name => $var): ?>
                <table class="table">
                    <caption>
                        <?= $name ?>
                    </caption>
                    <?php foreach ($var as $key => $value): ?>
                        <tr>
                            <td><?= $key ?></td>
                            <td>
                                <?php
                                if (is_array($value) || is_object($value)) {
                                    echo json_encode($value);
                                } else if (is_bool($value)) {
                                    echo $value ? "true" : "false";
                                } else if (is_null($value)) {
                                    echo "null";
                                } else if (is_string($value) || is_numeric($value)) {
                                    echo $value;
                                } else {
                                    echo "resource";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    <?php
    $scripts = [
        'js/prism.js'
    ];

    foreach ($scripts as $script) {
        echo file_get_contents($data['resource_path'] . '/' . $script);
    }
    ?>
</script>
<script type="text/javascript">
    function highlightReady(fn) {
        document.querySelector('.line-highlight') ? fn() : setTimeout(highlightReady.bind(this, fn), 1);
    }

    highlightReady(function () {
        const resizeEvent = new Event('resize');
        window.dispatchEvent(resizeEvent);
    });

    document.querySelectorAll(".toggle").forEach(function (node) {
        node.addEventListener("dblclick", function () {
            const content = node.getAttribute("title");
            node.setAttribute("title", node.innerHTML);
            node.innerHTML = content;
        });
    });
    document.querySelector(".err-msg").addEventListener("click", function () {
        const searchUrl = "https://www.baidu.com/s?wd=php " + this.innerHTML;
        window.open(searchUrl);
    });
</script>
</body>
</html>