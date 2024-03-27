<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte</title>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .task {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }

        .task h3 {
            margin-top: 0;
            color: #555;
        }

        .task p {
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Reporte de Tareas</h1>
        <h2>Para el periodo del {{ $startDate }} al {{ $endDate }}</h2>
        @foreach ($tasks as $task)
            <div class="task">
                <h3>{{ $task->title }}</h3>
                <p>Completado el {{ $task->completed_at }} por {{ $task->user->name }}</p>
                <p>{{ $task->description }}</p>
            </div>
        @endforeach

    </div>
</body>

</html>
