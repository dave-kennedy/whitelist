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
            <?php
                if ($viewData["actionResult"]["action"] == "saveConfig" && $viewData["actionResult"]["success"] == true) {
                    echo "<p class=\"result-success\" id=\"result\">Configuration saved.</p>";
                } elseif ($viewData["actionResult"]["action"] == "syncConfig" && $viewData["actionResult"]["success"] == true) {
                    echo "<p class=\"result-success\" id=\"result\">Configuration synced.</p>";
                } elseif ($viewData["actionResult"]["action"] == "uploadConfig" && $viewData["actionResult"]["success"] == true) {
                    "<p class=\"result-success\" id=\"result\">Configuration uploaded.</p>";
                } elseif ($viewData["actionResult"]["action"] == "uploadConfig" && $viewData["actionResult"]["success"] == false) {
                    "<p class=\"result-error\" id=\"result\">An error occurred while uploading the configuration.</p>";
                }
            ?>
            <form action="index.php" id="form" method="post">
                <input id="action" name="action" type="hidden" />
                <div id="controls">
                    <div style="float: left;">
                        <span id="add-category" tabIndex="1">Add</span>
                        <span id="view-exceptions" tabIndex="2">Exceptions</span>
                    </div>
                    <div style="float: right;">
                        <span id="save-config" tabIndex="3">Save</span>
                        <span id="sync-config" tabIndex="4">Sync</span>
                        <span id="upload-config" tabIndex="5">Upload</span>
                    </div>
                </div>
                <div id="categories">
                    <ul>
                        <?php
                            $i = 1;
                            foreach ($viewData["categories"] as $title => $contents) {
                                echo "<li><a href=\"#category-$i\">$title</a></li>";
                                $i++;
                            }
                        ?>
                    </ul>
                    <?php
                        $i = 1;
                        foreach ($viewData["categories"] as $title => $contents) {
                            echo "<div id=\"category-$i\">
                                      <p>
                                          <input class=\"category-title\" name=\"category-$i" . "[title]\" type=\"text\" value=\"$title\" />
                                          <span class=\"delete-category\">Delete</span>
                                      </p>
                                      <p>
                                          <textarea class=\"category-contents\" name=\"category-$i" . "[contents]\">" . implode($contents) . "</textarea>
                                      </p>
                                  </div>";
                            $i++;
                        }
                    ?>
                </div>
            </form>
        </div>
        <div id="add-category-modal">
            <p>Enter the name of the category you would like to add.</p>
            <p>
                <input id="add-category-modal-title" placeholder="New category" type="text" />
            </p>
            <p style="text-align: right;">
                <span id="add-category-modal-ok">Ok</span>
                <span id="add-category-modal-cancel">Cancel</span>
            </p>
        </div>
        <div id="view-exceptions-modal">
            <p>If you wish to deny access to part of a domain (subdomain), enter it as an exception below.</p>
            <p>
                <textarea id="view-exceptions-modal-contents" name="exceptions"><?php echo implode($viewData["exceptions"]); ?></textarea>
            </p>
            <p style="text-align: right;">
                <span id="view-exceptions-modal-ok">Ok</span>
            </p>
        </div>
        <div id="sync-config-modal">
            <p>By syncing the configuration with the DNS server, all local changes that have not been uploaded will be lost.</p>
            <p>Would you like to continue?</p>
            <p style="text-align: right;">
                <span id="sync-config-modal-ok">Ok</span>
                <span id="sync-config-modal-cancel">Cancel</span>
            </p>
        </div>
        <div id="upload-config-modal">
            <p>You must enter the administrator password to upload the configuration to the DNS server.</p>
            <p>
                <input id="upload-config-modal-password" placeholder="Password" type="password" />
            </p>
            <p style="text-align: right;">
                <span id="upload-config-modal-ok">Ok</span>
                <span id="upload-config-modal-cancel">Cancel</span>
            </p>
        </div>
        <script src="js/jquery-2.0.3.min.js"></script>
        <script src="js/jquery-ui-1.10.3.min.js"></script>
        <script src="js/jquery-ui-vertabs.min.js"></script>
        <script src="js/site.js"></script>
    </body>
</html>
