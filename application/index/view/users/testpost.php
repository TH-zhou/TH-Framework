<html>
<head>
    <title>测试CSRF</title>
</head>
<body>
<form action="{:url('index/Users/testPost')}" method="post">
    {CSRF_TOKEN}
    <input type="text" name="name" value="csrf" />
    <input type="text" name="tel" value="12345678900" />
    <input type="submit" />
</form>
</body>
</html>