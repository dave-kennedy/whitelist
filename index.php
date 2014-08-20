<?php require("dnsmasq.php"); ?>
<!doctype html>
<html>
    <head>
        <title>dnsmasq Configuration</title>
        <meta charset="utf-8" />
        <link href="css/flick/jquery-ui-1.10.3.min.css" rel="stylesheet" />
        <link href="css/jquery-ui-vertabs.min.css" rel="stylesheet" />
        <link href="css/site.css" rel="stylesheet" />
    </head>
    <body>
        <?php
            if (isset($actionResult)) {
                echo "<div class=\"" . ($actionResult["success"] ? "success" : "error") . "\" id=\"action-result\">
                          <p>" . $actionResult["message"] . "</p>
                          <p>
                              <a id=\"action-result-dismiss\">Click to close this message</a>.
                          </p>
                      </div>";
            }
        ?>
        <div id="container">
            <div class="ui-corner-bottom ui-state-default" id="header">
                <h2>
                    <a href="index.php">
                        <img src="dnsmasq.png" /> dnsmasq Configuration
                    </a>
                </h2>
            </div>
            <form action="index.php" id="form" method="post">
                <input id="action" name="action" type="hidden" />
                <div class="controls">
                    <div style="float: left;">
                        <span id="add-category" tabIndex="1">Add</span>
                    </div>
                    <div style="float: right;">
                        <span id="save-config" tabIndex="2">Save</span>
                        <span id="sync-config" tabIndex="3">Sync</span>
                        <span id="upload-config" tabIndex="4">Upload</span>
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
                <input id="add-category-modal-title" placeholder="New category" tabIndex="11" type="text" />
            </p>
            <p style="text-align: right;">
                <span id="add-category-modal-ok" tabIndex="12">Ok</span>
                <span id="add-category-modal-cancel" tabIndex="13">Cancel</span>
            </p>
        </div>
        <div id="sync-config-modal">
            <p>By syncing the configuration with the DNS server, all local changes that have not been uploaded will be lost.</p>
            <p>Would you like to continue?</p>
            <div class="controls">
                <div style="float: left;">
                    <span id="sync-compare" tabIndex="21">Compare</span>
                </div>
                <div style="float: right;">
                    <span id="sync-config-modal-ok" tabIndex="22">Ok</span>
                    <span id="sync-config-modal-cancel" tabIndex="23">Cancel</span>
                </div>
            </div>
        </div>
        <div id="upload-config-modal">
            <p>You must enter the administrator password to upload the configuration to the DNS server.</p>
            <p>
                <input id="upload-config-modal-password" name="password" placeholder="Password" tabIndex="31" type="password" />
            </p>
            <div class="controls">
                <div style="float: left;">
                    <span id="upload-compare" tabIndex="33">Compare</span>
                </div>
                <div style="float: right;">
                    <span id="upload-config-modal-ok" tabIndex="34">Ok</span>
                    <span id="upload-config-modal-cancel" tabIndex="35">Cancel</span>
                </div>
            </div>
        </div>
        <div id="sync-compare-modal">
            <p>By syncing the configuration with the DNS server, the following changes will be made to the local configuration file:</p>
            <h3>Additions</h3>
            <?php
                foreach ($viewData["remoteOnly"] as $title => $contents) {
                    if (empty($contents)) {
                        continue;
                    }
                    
                    echo "<h4>$title</h4>";
                    echo "<div class=\"add\">" . implode("</div><div class=\"add\">", $contents) . "</div>";
                }
            ?>
            <h3>Deletions</h3>
            <?php
                foreach ($viewData["localOnly"] as $title => $contents) {
                    if (empty($contents)) {
                        continue;
                    }
                    
                    echo "<h4>$title</h4>";
                    echo "<div class=\"delete\">" . implode("</div><div class=\"delete\">", $contents) . "</div>";
                }
            ?>
        </div>
        <div id="upload-compare-modal">
            <p>By uploading the configuration to the DNS server, the following changes will be made to the remote configuration file:</p>
            <h3>Additions</h3>
            <?php
                foreach ($viewData["localOnly"] as $title => $contents) {
                    if (empty($contents)) {
                        continue;
                    }
                    
                    echo "<h4>$title</h4>";
                    echo "<div class=\"add\">" . implode("</div><div class=\"add\">", $contents) . "</div>";
                }
            ?>
            <h3>Deletions</h3>
            <?php
                foreach ($viewData["remoteOnly"] as $title => $contents) {
                    if (empty($contents)) {
                        continue;
                    }
                    
                    echo "<h4>$title</h4>";
                    echo "<div class=\"delete\">" . implode("</div><div class=\"delete\">", $contents) . "</div>";
                }
            ?>
        </div>
        <script src="js/jquery-2.0.3.min.js"></script>
        <script src="js/jquery-ui-1.10.3.min.js"></script>
        <script src="js/jquery-ui-vertabs.min.js"></script>
        <script src="js/site.js"></script>
    </body>
</html>
