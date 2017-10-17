<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $this->body->title;?></title>
        <meta charset="utf-8">
        <meta name="title" content="<?php echo $this->body->title;?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="/favicon.png" type="image/x-icon">
        <link rel="shortcut icon" href="/favicon.png" type="image/x-icon">
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:200" rel="stylesheet">
        <?php echo $this->body->header;?>
    </head>
    <body>
        <div class="content">
        <?php
            echo $this->body;
        ?>
        </div>
    </body>
</html>
