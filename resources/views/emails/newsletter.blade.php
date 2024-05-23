
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<h1>{{ translate('Message') }}</h1>
<span style="font-size: 12px;">{{ $content }}</span>
<h4>{{ $content }}</h4>
@if(!empty($email))
<a class="btn btn-primary btn-md" href="{{route('unsubscribe',$email)}}">Unsubscribe</a>
@endif
</body>
</html>

