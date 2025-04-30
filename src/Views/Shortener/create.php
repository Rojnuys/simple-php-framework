<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Create code for url</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<div class="container mt-5">
    <a href="/" class="btn btn-success">Back</a>

    <div class="row">
        <form class=" mt-5" action="/encode" method="post">
            <label for="url">Url:</label>
            <input class="form-control mt-3" type="text" name="url" id="url">
            <input type="submit" value="Encode" class="btn btn-success mt-3">
        </form>
    </div>
</div>

</body>
</html>