<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/bootstrap/icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    <title><?= $data['page_title'] ?></title>
</head>

<body>
    <div id="offline-warning">
        <p> Offline vibes—waiting for that sweet connection. <center>Stay tuned!</center></p>
        <span id="close-btn" onclick="closeWarning()">
            <img src="assets\images\icons8-close-window.gif" alt="Close" style="width: 25px; height: 25px;">
        </span>
    </div>
    