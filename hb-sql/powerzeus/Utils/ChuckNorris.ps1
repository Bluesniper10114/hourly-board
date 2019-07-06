function ChuckNorrisQuote()
{
	$request = Invoke-WebRequest -Uri https://api.chucknorris.io/jokes/random?category=dev
	$joke = ConvertFrom-Json $request

	$line = $joke.value
	return $line;
}