<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<script>
	(() => {
		const preference = localStorage.getItem('theme') ?? 'system';
		const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
		const isDark = preference === 'dark' || (preference === 'system' && systemDark);
		document.documentElement.classList.toggle('dark', isDark);
	})();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
