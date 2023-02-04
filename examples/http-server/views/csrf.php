<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSRF</title>
</head>
<body>
<form action="/csrf" method="post">
    {!! \Larmias\Http\CSRF\csrf_field() !!}
    <input type="submit" value="发送"/>
</form>
</body>
</html>