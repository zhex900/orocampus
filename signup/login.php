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
        <form action="auth.php" method="post">

            <div class="form-title">
                <h2>CSAC Sign-up Account Login</h2>
            </div><!-- end .form-title section -->

            <div class="form-enclose">
                <div class="form-section">

                    <!-- Error message section -->
                    <!-- An error message is only displayed when needed, for example on failed login -->
                    <?php if( isset($_SESSION['error']) ): ?>
                        <section id="error-message"><?php echo $_SESSION['error'] ?></section>
                        <?php
                        // unsetting the session variable 'error' so that error messages aren't
                        // displayed on the login page if the user refreshes the page
                        unset($_SESSION['error']);
                    endif;
                    ?>

                    <!-- Username entry section -->
                    <section>
                        <label for="usernames" class="lbl-text">Username:</label>
                        <label class="lbl-ui append-icon">
                            <input type="text" name="usernames" id="usernames" class="input" placeholder="Enter Username" autofocus required />
                            <span><i class="icon-user"></i></span>
                        </label>
                    </section>

                    <!-- Password entry section -->
                    <section>
                        <label for="pass" class="lbl-text">Password:</label>
                        <label class="lbl-ui append-icon">
                            <input type="password" name="pass" id="pass" class="input" placeholder="Enter Password" required/>
                            <span><i class="icon-lock"></i></span>
                        </label>
                    </section>

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
                        <label for="source_of_contact" class="lbl-text">Contact Method:</label>
                        <label for="source_of_contact" class="lbl-ui select">
                            <select id="source_of_contact" name="source_of_contact">
                                <!-- value must be the same as the source of contact values in zurmo -->
                                <option value="Table">Table</option>
                                <option value="Bible Study">Bible Study</option>
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
                    <input type="Submit" name="Form1_Submit"  class="button blue" value="Login">
                </section>
            </div><!-- end .form-buttons section -->

        </form>
    </div><!-- end .golden-forms section -->
</div><!-- end .gforms section -->

<div></div><!-- end section -->
<div></div><!-- end section -->

</body>
</html>
