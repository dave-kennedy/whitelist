<?php require("dnsmasq.php"); ?>
<!doctype html>
<html>
    <head>
        <title>dnsmasq Configuration</title>
        <link href="css/flick/jquery-ui-1.10.3.min.css" rel="stylesheet" />
        <link href="css/jquery-ui-vertabs.min.css" rel="stylesheet" />
        <link href="css/site.css" rel="stylesheet" />
    </head>
    <body>
        <div id="container">
            <div class="ui-corner-bottom ui-state-default" id="header">
                <h2>
                    <a href="index.php">
                        <img src="dnsmasq.png" /> dnsmasq Configuration
                    </a>
                </h2>
            </div>
            <?php echo $saveResult; ?>
            <?php echo $uploadResult; ?>
            <p>
                To add a new category, click the "+ New" tab on the left. To delete a category, remove all of the URLs
                from the textbox on the right.
            </p>
            <p>
                Click the save button below to save the configuration locally. To upload the configuration and restart
                the DNS server, enter the password and click the upload button.
            </p>
            <form action="index.php" id="form" method="post">
                <input id="action" name="action" type="hidden" />
                <div id="categories">
                    <ul>
                        <?php echo $categoryTabs; ?>
                        <li><a href="#new-category">+ New</a></li>
                    </ul>
                    <?php echo $categoryDivs; ?>
                    <div id="new-category">
                        <p><input class="new-category-title" placeholder="New category" type="text" /></p>
                        <p><textarea class="new-category-contents"></textarea></p>
                    </div>
                </div>
                <p style="text-align: right;">
                    <input class="button" id="save" type="button" value="Save" />
                    <input class="button" id="upload" type="button" value="Upload" />
                    <input class="password" id="password" placeholder="Password" type="password" />
                </p>
            </form>
        </div>
        <script src="js/jquery-2.0.3.min.js"></script>
        <script src="js/jquery-ui-1.10.3.min.js"></script>
        <script src="js/jquery-ui-vertabs.min.js"></script>
        <script src="js/site.js"></script>
    </body>
</html>
