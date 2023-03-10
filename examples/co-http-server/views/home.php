<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>首页</title>
</head>
<body>
{{ $welcome }}
{{ json_encode($data ?? []) }}
</body>
</html>