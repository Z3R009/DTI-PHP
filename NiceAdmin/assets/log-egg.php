<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .easter-egg {
            text-decoration: none;
            color: inherit;
            opacity: 0;
            pointer-events: none;
            margin-left: 4px;
            font-weight: bold;
            transition: opacity 0.3s ease;
        }

        .easter-egg.visible {
            opacity: 1;
            pointer-events: auto;
            color: #ff6600;
        }

        .easter-modal {
            display: none;
            position: fixed;
            bottom: 60px;
            right: 20px;
            background-color: white;
            color: #333;
            padding: 16px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 250px;
            font-family: Arial, sans-serif;
        }

        .easter-modal-content ul {
            padding-left: 20px;
            margin: 10px 0;
        }

        .easter-modal-content li {
            list-style-type: disc;
        }

        .close-easter {
            float: right;
            cursor: pointer;
            color: #aaa;
            font-size: 18px;
        }

        .close-easter:hover {
            color: #ff6600;
        }
    </style>

</head>

<body>
    <div class="easter-modal-content">
        <span class="close-easter" onclick="document.getElementById('easterModal').style.display='none'">&times;</span>
        <h4>Created By:</h4>
        <ul>
            <li><a href="https://web.facebook.com/marcnelson.dorato.1" target="_blank">Marc Nelson Dorato</a></li>
            <li><a href="https://web.facebook.com/garzonjohn" target="_blank">John Abner Garzon</a></li>
            <li><a href="https://web.facebook.com/ritz.larano.5/" target="_blank">Ritz Laraño</a></li>
            <li><a href="https://web.facebook.com/search/top?q=james%20zyrus%20pama" target="_blank">James Zyrus
                    Pama</a></li>
        </ul>
    </div>
</body>




</html>

<!-- creators.php -->