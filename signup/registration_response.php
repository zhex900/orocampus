<?php
    // starting a session to enable session variables to be stored
    session_start();

    // only displaying the page if the user has logged in (i.e. the user has been assigned a sessionID by zurmo)
    if(isset($_SESSION['form'])):
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title> Registration Success | Curtin Christians </title>

    <link type="text/css" rel="stylesheet" href="css/golden-forms.css"/>
    <link type="text/css" rel="stylesheet" href="css/font-awesome.min.css"/>

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

</head>

<body class="bg-wooden">

<div class="gforms">

    <div class="golden-forms wrapper mini">

        <div class="form-title">
            <h2>
                <?php if (isset($_SESSION['response_msg_title'])) {
                    echo $_SESSION['response_msg_title'];
                    unset($_SESSION['response_msg_title']);
                }
                ?>
            </h2>
        </div><!-- end .form-title section -->

            <div class="form-enclose">
                <div class="form-section">

                    <section>
                        <br>
                        <?php if (isset($_SESSION['response_msg'])): ?>
                            <p id="response_message"><?php echo $_SESSION['response_msg'] ?></p>
                            <?php
                            unset($_SESSION['response_msg']);
                        endif;
                        ?>
                        <br>
                        <br>
                        <br>
                    </section>

                </div><!-- end .form-section section -->
            </div><!-- end .form-enclose section -->

            <!-- Form button section -->
            <div class="form-buttons">
                <section>
                    <!-- assuming that a form has been selected since the user has already logged in -->
                    <a class="button blue" href="<?php echo $_SESSION['form'] ?>">Next registration</a>
                </section>
            </div><!-- end .form-buttons section -->
    </div><!-- end .golden-forms section -->
</div><!-- end .gforms section -->

<div></div>
<!-- end section -->
<div></div>
<!-- end section -->

</body>
</html>

<?php
    // if the user is not logged in then redirect them to the login page
    else:
        header("Location: login.php");
    endif;
?>