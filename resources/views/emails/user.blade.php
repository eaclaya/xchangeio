<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Email</title>
</head>
<body>
    <div style="font-family: sans-serif; font-size: 1em;">
        <h2>Welcome!</h2>
        <p>Use the password below to login:</p>
        <p>Email: <strong>{{$user->email}}</strong></p>
        <p>Password: <strong>{{$password}}</strong></p>
        <p>Dashboard: <a href="http://localhost:8001">http://localhost:8001</a></p>
    </div>
</body>
</html>