<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Shortener</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<div class="container mt-5">
    <a href="/create" class="btn btn-success">Create code</a>

    <table class="mt-5 table">
        <tr>
            <th>ID</th>
            <th>URL</th>
            <th>CODE</th>
            <th>COUNT</th>
        </tr>
    <?php foreach ($urlCodePairs ?? [] as $urlCodePair): ?>
        <tr>
            <td><?= $urlCodePair->getId() ?></td>
            <td><?= $urlCodePair->getUrl() ?></td>
            <td><a href="/decode/<?= $urlCodePair->getCode() ?>"><?= $urlCodePair->getCode() ?></a></td>
            <td><?= $urlCodePair->getCount() ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
</div>
</body>
</html>