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
                To add a new category, enter the name below and click the add button. To remove a category, click the
                delete button next to the category name.
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
                    </ul>
                    <?php echo $categoryDivs; ?>
                </div>
                <div>
                    <div style="float: left;">
                        <input id="new-category" placeholder="New category" tabIndex="1" type="text" />
                        <span id="add-category" tabIndex="2">Add</span>
                    </div>
                    <div style="float: right;">
                        <span id="save-config" tabIndex="3">Save</span>
                        <span id="upload-config" tabIndex="4">Upload</span>
                        <input id="password" name="password" placeholder="Password" tabIndex="5" type="password" />
                    </div>
                </div>
            </form>
        </div>
        <script src="js/jquery-2.0.3.min.js"></script>
        <script src="js/jquery-ui-1.10.3.min.js"></script>
        <script src="js/jquery-ui-vertabs.min.js"></script>
        <script src="js/site.js"></script>
    </body>
</html>
