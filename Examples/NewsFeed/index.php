<?php

/**
 * News feed example by Chiel
 * 
 * In this example Xantase is generating news items in a news feed based
 * on a JSON feed. I programmed 2 modules in Xantase, the news wrapper and the news item.
 * The news wrapper makes a new div and places the news items in the generated div the
 * news item is generating, well... the news item!
 * 
 */


include_once "../../xantase.php";
$xantase = new Xantase();

?>

<html>

<head>
    <title>News feed example</title>

    <!-- xantase newsWrapper and NewsItem modules -->
    <script type="text/javascript">
        <?= $xantase->xantase_build(__DIR__ . '/xantase'); ?>
    </script>

    <style>
        #news-feed {
            display: flex;
            gap: 1em;
        }

        #news-feed .news-card {
            padding: 2em;
            border: 1px solid #DEDEDE;
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>

</head>

<body>

    <div id="news-feed"></div>
    
    <script>

        // the JSON feed, I used a local one, maybe you can try a GET request? :)
        let api_json_response = [{
                "title": "Xantase news feed!",
                "content": "Super easy to use, lets make more!"
            },
            {
                "title": "Oh hello, Im a news item!",
                "content": "Just look at how clean I'm loaded in with Xantase. Try out yourself!"
            },
            {
                "title": "Look at me!",
                "content": "I am a Xantasa loaded news article, unbelievable right??"
            }
        ];


        // Initialize Xantase in Javascript and give the instance the api response
        var xantase = new Xantase();
        xantase.build(NewsWrapper, "#news-feed", api_json_response);
    </script>

</body>

</html>