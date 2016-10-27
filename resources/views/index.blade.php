<!DOCTYPE html>
<html>
<head>

</head>
<body>
<h1>
    Early: {{ $view['early']->count() }}
</h1>
<h1>
    On-Time: {{ $view['on-time']->count() }}
</h1>
<h1>
    Late: {{ $view['late']->count() }}
</h1>
</body>
</html>
