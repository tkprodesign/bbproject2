<?php
$things = [
    'A cloud shaped like a dragon',
    'A lucky penny on the sidewalk',
    'A cup of perfectly brewed coffee',
    'A playlist that matches your mood',
    'An unexpected compliment',
    'A sunset worth stopping for',
    'A great idea in the middle of the night',
    'A tiny plant growing through concrete',
    'A message from an old friend',
    'A quiet moment that feels just right',
];

$randomThing = $things[array_rand($things)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Random Thing</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
            padding: 32px;
            max-width: 560px;
            text-align: center;
        }

        h1 {
            margin-top: 0;
        }

        .thing {
            font-size: 1.3rem;
            font-weight: 700;
            margin: 18px 0 26px;
        }

        a {
            display: inline-block;
            text-decoration: none;
            color: #fff;
            background: #2563eb;
            border-radius: 8px;
            padding: 10px 16px;
        }

        a:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Here's a Random Thing</h1>
        <p class="thing"><?php echo htmlspecialchars($randomThing, ENT_QUOTES, 'UTF-8'); ?></p>
        <a href="">Show me another</a>
    </main>
</body>
</html>
