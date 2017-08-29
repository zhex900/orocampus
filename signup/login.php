<?php
// starting a session to enable session variables to be stored
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title> Account Login  | Curtin Christians </title>

    <link type="text/css" rel="stylesheet"   href="css/golden-forms.css"/>
    <link type="text/css" rel="stylesheet"   href="css/font-awesome.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css">

    <!--[if lte IE 9]>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="js/jquery.placeholder.min.js"></script>
    <![endif]-->

    <!--[if IE 9]>
    <link type="text/css" rel="stylesheet" href="css/golden-forms-ie9.css">
    <![endif]-->

    <!--[if lte IE 8]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <link type="text/css" rel="stylesheet" href="css/golden-forms-ie8.css">
    <![endif]-->
    <script src="http://code.jquery.com/jquery-3.2.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <script src="./js/mySelect2.js"></script>

    <!-- Use jquery to load contact source json file and fill the drop down-->
    <script>
        $(document).ready(function() {
            var json =
                <?php
                //load drop-down values
                $file = './data/data.json';
                if (!is_file($file) || !is_readable($file)) {
                    die("File not accessible.");
                }
                $contents = file_get_contents($file);
                $_SESSION['dataCache'] = json_decode($contents, true);

                echo json_encode($_SESSION['dataCache']);
                ?>;
            mySelect2(json.contactsourcesources,'#source_of_contact');
        });
    </script>

</head>

<body class="bg-wooden">

<div class="gforms">

    <div class="golden-forms wrapper mini">
        <form action="init.php" method="post">

            <div class="form-title">
                <h2>CSAC Sign-up Account Login</h2>
            </div><!-- end .form-title section -->

            <div class="form-enclose">
                <div class="form-section">

                    <!-- Form selection section -->
                    <section>
                        <label for="form_types" class="lbl-text">Form:</label>
                        <label for="form_types" class="lbl-ui select">
                            <select id="form_types" name="form_types">
                                <!-- value must be the actual file name of the form. -->
                                <option value="club_registration.php">New member sign up form</option>
                            	<option value="members_registration.php">Club member sign up form</option>
			    </select>
                        </label>
                    </section>

                    <!-- Source selection section -->
                    <section>
                        <label for="source_of_contact">
                            Contact Method:
                            <select class="source_of_contact" id="source_of_contact" name="source_of_contact">
                                <!-- value must be the same as the source of contact values in zurmo -->
                            </select>
                        </label>
                    </section>

                </div><!-- end .form-section section -->
            </div><!-- end .form-enclose section -->

            <!-- Form button section -->
            <div class="form-buttons">
                <section>
                    <!-- Forms original buttons. Left for reference -->
                    <!-- <button class="button blue">Login</button> -->
                    <!--<a class="button red" href="#">Register</a>-->
                    <input type="Submit" name="login"  class="button blue" value="Login">
                    <input type="Submit" name="reload" class="button" value="Reload details">
                </section>
            </div><!-- end .form-buttons section -->

        </form>
    </div><!-- end .golden-forms section -->
</div><!-- end .gforms section -->

<div></div><!-- end section -->
<div></div><!-- end section -->

</body>
</html>
