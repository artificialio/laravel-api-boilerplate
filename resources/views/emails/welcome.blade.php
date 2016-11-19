<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
    <h2>Welcome</h2>
    <p>
        An account has been created for you, please click <a href="{{url('sign-up?token='.$user->token)}}">here</a> to create your password.
    </p>
</body>
</html>