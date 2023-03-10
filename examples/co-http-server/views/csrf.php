<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSRF</title>
</head>
<body>
{{ $welcome }}
{{ json_encode($data ?? []) }}
</body>
</html>