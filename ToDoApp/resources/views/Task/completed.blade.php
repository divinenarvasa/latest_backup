<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Completed Tasks</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Indie+Flower&display=swap">
    <style>
        body {
            background-color: #FFF9D0;
            font-family: 'Indie Flower', cursive;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #5AB2FF;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            width: 100%;
            overflow-x: auto;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .completed-task {
            color: green;
            font-weight: bold;
        }
        .description-cell {
            word-wrap: break-word;
            white-space: normal;
            max-width: 300px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Completed Tasks</h1>
    </header>
    <div class="container">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Completed At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($completedTodos as $todo)
                        <tr>
                            <td class="completed-task">{{ $todo->title }}</td>
                            <td class="description-cell completed-task">{{ $todo->description }}</td>
                            <td class="completed-task">{{ $todo->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($completedTodos->isEmpty())
                <h4>No Completed Tasks</h4>
            @endif
        </div>
    </div>
</body>
</html>
