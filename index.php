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
            textarea {
                height: 20em;
                width: 100%;
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
            <form action="index.php" id="upload-form" method="post">
                <input id="action" name="action" type="hidden" />
                <input id="password" name="password" type="hidden" />
                <div id="categories">
                    <ul>
                        <?php echo $categoryTabs; ?>
                        <li id="new-category-tab"><a href="#new-category">+ New</a></li>
                    </ul>
                    <?php echo $categoryDivs; ?>
                    <div id="new-category">
                        <p><input placeholder="New category" type="text" /></p>
                        <p><textarea></textarea></p>
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
                    form = $('#upload-form'),
                    password = $('#password'),
                    prompt = $('#upload-prompt'),
                    name,
                    newName;
                
                $('.ui-vertabs-panel input').focus(function () {
                    name = $(this).val().trim();
                }).blur(function () {
                    newName = $(this).val().trim();
                    
                    if (name == '' && newName != '') {
                        categories.vertabs('addTab', newName);
                        $(this).val('');
                        return;
                    }
                    
                    if (name != '' && newName != '' && name != newName) {
                        categories.vertabs('renameTab', name, newName);
                        return;
                    }
                });
                
                $('#upload-prompt-password').blur(function () {
                    password.val($(this).val());
                });
                
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
                
                $('#result').hide().fadeIn().delay(3000).fadeOut();
            });
        </script>
    </body>
</html>
