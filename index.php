<?php require("dnsmasq.php"); ?>
<!doctype html>
<html>
    <head>
        <title>dnsmasq Configuration</title>
        <link href="css/flick/jquery-ui-1.10.3.min.css" rel="stylesheet" />
        <link href="css/jquery-ui-vertabs.css" rel="stylesheet" />
        <style>
            body {
                font-family: Verdana,Arial,sans-serif;
                margin: 0;
            }
            #container {
                margin: 0 auto;
                width: 75%;
            }
            #header {
                padding: 0 1em;
            }
            #header img {
                vertical-align: top;
            }
            #result {
                border: 1px solid;
                border-radius: 2px;
                padding: 0.5em;
            }
            .result-success {
                background-color: #efe;
                color: #090;
            }
            .result-error {
                background-color: #fee;
                color: #c00;
            }
            .category-contents, .new-category-contents {
                height: 20em;
                width: 100%;
            }
        </style>
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
                To add a new category, click the "+ New" tab on the left. To delete a category,
                remove all of the URLs from the textbox on the right.
            </p>
            <form action="index.php" id="form" method="post">
                <input id="action" name="action" type="hidden" />
                <input id="password" name="password" type="hidden" />
                <div id="categories">
                    <ul>
                        <?php echo $categoryTabs; ?>
                        <li id="new-category-tab"><a href="#new-category">+ New</a></li>
                    </ul>
                    <?php echo $categoryDivs; ?>
                    <div id="new-category">
                        <p><input class="new-category-title" placeholder="New category" type="text" /></p>
                        <p><textarea class="new-category-contents"></textarea></p>
                    </div>
                </div>
                <p style="text-align: right;">
                    <input class="button" id="save-button" type="button" value="Save" />
                    <input class="button" id="upload-button" type="button" value="Upload" />
                </p>
            </form>
        </div>
        <div id="upload-prompt" title="Upload">
            <p>Please enter the password to continue:</p>
            <p><input id="upload-prompt-password" type="password" /></p>
        </div>
        <script src="js/jquery-2.0.3.min.js"></script>
        <script src="js/jquery-ui-1.10.3.min.js"></script>
        <script src="js/jquery-ui-vertabs.js"></script>
        <script>
            $(function () {
                var action = $('#action'),
                    categories = $('#categories').vertabs(),
                    form = $('#form'),
                    prompt = $('#upload-prompt'),
                    title;
                
                $('body').focusin(function (e) {
                    var target = $(e.target);
                    
                    if (target.hasClass('category-title')) {
                        title = target.val().trim();
                        return;
                    }
                }).focusout(function (e) {
                    var target = $(e.target),
                        newTitle;
                    
                    if (target.hasClass('category-title')) {
                        newTitle = target.val().trim();
                        categories.vertabs('renameTab', title, newTitle);
                        return;
                    }
                    
                    if (target.hasClass('new-category-title')) {
                        newTitle = target.val().trim();
                        categories.vertabs('addTab', newTitle);
                        return;
                    }
                    
                    if (target.is('#upload-prompt-password')) {
                        $('#password').val(target.val());
                        return;
                    }
                });
                
                $('#save-button').click(function (e) {
                    e.preventDefault();
                    action.val('save');
                    form.submit();
                }).button();
                
                $('#upload-button').click(function (e) {
                    e.preventDefault();
                    action.val('upload');
                    prompt.dialog('open');
                }).button();
                
                prompt.dialog({
                    'autoOpen': false,
                    'buttons': {
                        'Ok': function () {
                            form.submit();
                        },
                        'Cancel': function () {
                            $(this).dialog('close');
                        }
                    },
                    'modal': true
                });
                
                $('#result').hide().fadeIn().delay(3000).fadeOut();
            });
        </script>
    </body>
</html>
