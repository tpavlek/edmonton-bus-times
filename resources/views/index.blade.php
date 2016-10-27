<!DOCTYPE html>
<html>
<head>

</head>
<body>
<form action="/" method="get">
    <label for="early">
        Early threshold (seconds):
        <input type="number" name="early" value="{{ Request::get('early', 180) }}" />
    </label>

    <label for="late">
        Late Threshold (seconds):
        <input type="number" name="late" value="{{ Request::get('late', 180) }}" />
    </label>

    <input type="submit" value="Go" />


</form>

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
