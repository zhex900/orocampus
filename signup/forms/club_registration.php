<?php
// starting a session to enable session variables to be stored
session_start();

// only displaying the page if the user has logged in (i.e. the user has been assigned a sessionID by zurmo)
if (isset($_SESSION['form'])):
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

        <title> Registration Form | UNSW Christians </title>
        <link rel="stylesheet" type="text/css" href="../css/select2-4.0.3.css">
        <link rel="stylesheet" type="text/css" media="all" href="../css/auto-complete-style.css">
        <link type="text/css" rel="stylesheet" href="../css/golden-forms.css"/>
        <link type="text/css" rel="stylesheet" href="../css/font-awesome.min.css"/>
        <link rel="stylesheet" href="../css/jquery-ui-1.11.0.css">
        <script src="../js/jquery-1.12.4.min.js"></script>
        <script src="../js/jquery-ui-1.12.1.min.js"></script>
        <!--<script src="../js/select2-4.0.3.min.js"></script>-->
        <script src="../js/select2-4.0.3.js"></script>

        <!-- Automatically completes the address as the user is typing it -->
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDQpSg-uWZkbaydLNvkXcwhNvIA6BfffJQ&libraries=places&callback=initAutocomplete"
                async defer></script>

        <script src="../js/address-autocomplete.js"></script>
        <script src="../js/mySelect2.js"></script>
        <script type="text/javascript">
            $(function () {
                $("#datepicker").datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: 'dd/mm/yy',
                    yearRange: "-40:-5"
                });

                var json = <?php echo json_encode($_SESSION['dataCache']); ?>;
                mySelect2(json.levelofstudysources, '#degree');
                mySelect2(json.institutionssources, '#uni');
                mySelect2(json.countries, '#countryorigin');
                mySelect2(json.degreessources, '#course');
            });
        </script>

    </head>

    <body class="bg-wooden" onload="geolocate(); initialize();">

    <div class="gforms">

        <div class="golden-forms wrapper">
            <form name="inputForm" action="../register.php" method="post">

                <div class="form-title">
                    <img src="../images/CSAC4.jpg" alt="Christian Students at Curtin">
                    <!--  <h2>CSAC Registration</h2>-->
                </div>
                <!-- end .form-title section -->
                <div class="form-enclose">
                    <div class="form-section">

                        <!-- hidden fields containing form specific information -->
                        <!-- each hidden fields value is case sensitive and equal the values required by zurmo -->
                        <input type="hidden" name="churchkid" value="0"/>
                        <input type="hidden" name="form" value="newcontacts"/>
                        <!-- hidden fields used for parsing the user's entered address -->
                        <input type="hidden" id="street_number" name="street_number"/>
                        <input type="hidden" id="route" name="street_name"/>
                        <input type="hidden" id="locality" name="city"/>
                        <input type="hidden" id="administrative_area_level_1" name="state"/>
                        <input type="hidden" id="country" name="country"/>
                        <input type="hidden" id="postal_code" name="postal_code"/>

                        <!-- Enter name section -->
                        <section>
                            <div class="row">
                                <div class="col6 first">
                                    <label for="fname" class="lbl-text">First Name:<span id="required">*</span></label>
                                    <label class="lbl-ui">
                                        <input type="text" name="fname" id="fname" class="input" pattern="[a-zA-Z -]+"
                                               placeholder="Enter First Name" autofocus required autocomplete="off"/>
                                    </label>
                                </div>
                                <!-- end .col6 section -->
                                <div class="col6 last colspacer-two">
                                    <label for="lname" class="lbl-text">Last Name:<span id="required">*</span></label>
                                    <label class="lbl-ui">
                                        <input type="text" name="lname" id="lname" class="input" pattern="[a-zA-Z -]+"
                                               placeholder="Enter Last Name" required autocomplete="off"/>
                                    </label>
                                </div>
                                <!-- end .col6 section -->
                            </div>
                            <!-- end .row section-->
                        </section>

                        <!-- Enter student number email address section section -->
                        <section>
                            <div class="row">
                                <div class="col6 first">
                                    <label for="student_id" class="lbl-text">Student Number:<span id="required">*</span></label>
                                    <label class="lbl-ui">
                                        <input type="text" name="student_id" id="student_id" class="input"
                                               autocomplete="off"
                                               placeholder="Enter Student Number" pattern="[0-9]{7,8}" required
                                               title="7 or 8 digits."/>
                                    </label>

                                </div>
                                <!-- end .col6 section -->
                                <div class="col6 last colspacer-two">
                                    <label for="email" class="lbl-text">Email Address:<span
                                                id="required">*</span></label>
                                    <label class="lbl-ui">
                                        <input type="email" name="email" id="email" class="input" autocomplete="off"
                                               placeholder="Enter Email Address" required
                                               pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$"/>
                                    </label>
                                </div>
                                <!-- end .col6 section -->
                            </div>
                            <!-- end .row section-->
                        </section>

                        <!-- Gender and are you a Christian section -->
                        <section>
                            <div class="row">
                                <div class="col6 first">
                                    <label for="gender" class="lbl-text spacer">Gender / Sex:<span
                                                id="required">*</span></label>

                                    <div class="option-group">

                                    <span class="goption">
                                        <label class="options">
                                            <input id="gender_op1" name="gender" value="male" type="radio" required>
                                            <span class="radio"></span>
                                        </label>
                                        <label for="gender_op1">Male</label>
									</span><!-- end .goption section -->

                                        <span class="goption">
                                        <label class="options">
                                            <input id="gender_op2" name="gender" value="female" type="radio">
                                            <span class="radio"></span>
                                        </label>
                                        <label for="gender_op2">Female</label>
									</span><!-- end .goption section -->

                                    </div>
                                    <!-- end .option-group section -->
                                </div>
                                <!-- end .col6 section -->

                                <div class="col6 last colspacer-two">
                                    <label for="areyouchristian" class="lbl-text spacer">Are you a Christian:<span
                                                id="required">*</span></label>

                                    <div class="option-group">

                                    <span class="goption">
                                        <label class="options">

                                            <input id="areyouchristian_op1" name="areyouchristian" value=1 type="radio"
                                                   required/>
                                            <span class="radio"></span>
                                        </label>
                                        <label for="areyouchristian_op1">Yes</label>
									</span><!-- end .goption section -->

                                        <span class="goption">
                                        <label class="options">

                                            <input id="areyouchristian_op2" name="areyouchristian" value=0 type="radio">
                                            <span class="radio"></span>
                                        </label>
                                        <label for="areyouchristian_op2">No</label>
									</span><!-- end .goption section -->

                                    </div>
                                    <!-- end .option-group section -->
                                </div>
                                <!-- end .col6 section -->
                            </div>
                            <!-- end .row section-->
                        </section>
                        <!--  International student and country of origin section -->
                        <section>
                            <div class="row">
                                <div class="col6 first">
                                    <label for="international_student" class="lbl-text spacer">International
                                        Student:<span id="required">*</span></label>

                                    <div class="option-group">

                                    <span class="goption">
                                        <label class="options">
                                            <input id="int_student_op1" name="int_student" value=1 type="radio"
                                                   required/>
                                            <span class="radio"></span>
                                        </label>
                                        <label for="int_student_op1">Yes</label>
									</span><!-- end .goption section -->

                                        <span class="goption">
                                        <label class="options">
                                            <input id="int_student_op2" name="int_student" value=0 type="radio">
                                            <span class="radio"></span>
                                        </label>
                                        <label for="int_student_op2">No</label>
									</span><!-- end .goption section -->

                                    </div>
                                    <!-- end .option-group section -->
                                </div>
                                <!-- end .col6 section -->

                                <div class="col6 last colspacer-two">
                                    <label for="dob" class="lbl-text">Date of birth:</label>
                                    <label class="lbl-ui">
                                        <input type="text" name="dob" id="datepicker" class="input"
                                               placeholder="dd/mm/yyyy" autocomplete="off"/>
                                    </label>

                                    <!-- end .option-group section -->
                                </div>
                                <!-- end .col6 section -->
                            </div>
                            <!-- end .row section-->
                        </section>
                        <section>

                            <label for="countryorigin" class="lbl-text">Country of origin:<span id="required">*</span>
                                <select class="countryorigin" id="countryorigin" name="countryorigin">
                                </select>
                            </label>


                            <!-- end .row section-->
                        </section>
                        <!--  Enter address section -->
                        <section>
                            <label for="address" class="lbl-text">Address:<span id="required">*</span></label>
                            <label class="lbl-ui">
                                <input type="text" name="address" id="address" class="input" autocomplete="off"
                                       placeholder="Enter Address" required/>
                            </label>
                        </section>

                        <!--  Enter phone numbers section -->
                        <section>
                            <div class="row">
                                <div class="col6 first">
                                    <label for="mobile" class="lbl-text">Mobile:<span id="required">*</span></label>
                                    <label class="lbl-ui">
                                        <input type="tel" name="mobile" id="mobile" class="input"
                                               pattern="[0][4][0-9]{8,8}"
                                               placeholder="Enter Mobile Number" required autocomplete="off"
                                               title="10 digits and begins with 04"/>
                                    </label>
                                </div>
                                <!-- end .col6 section -->
                                <div class="col6 last colspacer-two">
                                    <label for="telephone" class="lbl-text">Telephone:</label>
                                    <label class="lbl-ui">
                                        <input type="tel" name="telephone" id="telephone" class="input"
                                               pattern="[0-9]{8,10}"
                                               placeholder="Enter Telephone Number" autocomplete="off"
                                               title="8-10 digits"/>
                                    </label>
                                </div>
                                <!-- end .col6 section -->
                            </div>
                            <!-- end .row section-->
                        </section>

                        <!-- Degree and major details section -->
                        <section>
                            <div class="row">
                                <div class="col6 first">
                                    <label for="degree" class="lbl-text">Degree Type:<span
                                                id="required">*</span></label>
                                    <select class="degree" id="degree" name="degree">
                                    </select>
                                </div>
                                <!-- end .col6 section -->

                                <div class="col6 last colspacer-two">
                                    <label for="course" class="lbl-text">Course:<span id="required">*</span></label>
                                        <select class="course" id="course" name="course">
                                        </select>
                                </div>
                                <!-- end .col6 section -->
                            </div>
                            <!-- end .row section-->
                        </section>

                        <!-- Semesters completed and university section -->
                        <section>
                            <div class="row">
                                <div class="col6 first">
                                    <label for="uni" class="lbl-text">University:<span id="required">*</span></label>
                                        <select class="uni" id="uni" name="uni">
                                        </select>
                                </div>
                                <!-- end .col6 section -->

                                <div class="col6 last colspacer-two">
                                    <label for="year" class="lbl-text">Year of Commencement:<span id="required">*</span></label>
                                    <label for="year" class="lbl-ui select">
                                        <select id="year" name="year">
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </label>
                                </div>
                                <!-- end .col6 section -->
                            </div>
                            <!-- end .row section-->
                        </section>

                        <!-- Divider line -->
                        <section>
                            <div class="gspace blank"></div>
                            <!-- end .gspace section -->
                        </section>

                        <!-- Activity enquiry section -->
                        <section>
                            <div class="option-group block">

							<span class="goption">
								<label class="options">
                                    <input type="checkbox" id="enquiry2" name="enquiries[]" value="further_activities"/>
                                    <span class="checkbox"></span>
                                </label>
								<label for="enquiry1">Please contact me for further activities</label>
							</span><!-- end .goption section -->

                                <span class="goption">
								<label class="options">
                                    <input type="checkbox" id="enquiry2" name="enquiries[]" value="bible_study"/>
                                    <span class="checkbox"></span>
                                </label>
								<label for="enquiry2">Please contact me for Bible study</label>
							</span><!-- end .goption section -->

                                <span class="goption">
								<label class="options">
                                    <input type="checkbox" id="enquiry3" name="enquiries[]" value="christian_courses"/>
                                        <span class="checkbox"></span>
                                </label>
								<label for="enquiry3">I would like to know about free Christian courses</label>
							</span><!-- end .goption section -->

                                <span class="goption">
								<label class="options">
                                    <input type="checkbox" id="enquiry4" name="enquiries[]" value="homes"/>
                                    <span class="checkbox"></span>
                                </label>
								<label for="enquiry4">I would like to attend dinner and fellowship in the homes</label>
							</span><!-- end .goption section -->

                                <span class="goption">
								<label class="options">
                                    <input type="checkbox" id="enquiry5" name="enquiries[]" value="mhl"/>
                                    <span class="checkbox"></span>
                                </label>
								<label for="enquiry5">I would like to know the meaning of human life</label>
							</span><!-- end .goption section -->

                                <span class="goption">
								<label class="options">
                                    <input type="checkbox" id="enquiry5" name="enquiries[]" value="sunday_worship"/>
                                    <span class="checkbox"></span>
                                </label>
								<label for="enquiry5">I would like attend Sunday worship</label>
							</span><!-- end .goption section -->

                            </div>
                            <!-- end .option-group section -->
                        </section>

                    </div>
                    <!-- end .form-section section -->
                </div>
                <!-- end .form-enclose section -->

                <!-- Button(s) section -->
                <div class="form-buttons">
                    <section>
                        <input type="submit" value="Submit Form" class="button blue"/>
                        <input type="reset" value="Reset Form" class="button"/>
                    </section>
                </div>
                <!-- end .form-buttons section -->

            </form>
        </div>
        <!-- end .golden-forms section -->
    </div>
    <!-- end .gforms section -->

    <div></div>
    <!-- end section -->
    <div></div>
    <!-- end section -->

    </body>

    <footer>
        <p id="user_logged_in"><?php echo $_SESSION['form'] . ' - ' . $_SESSION['event'] . ' ' . $_SESSION['eventCache'][$_SESSION['event']] ?></p>
        <p id="logout"><a href="../logout.php">Logout</a></p>
    </footer>
    </html>

    <?php
// if the user is not logged in then redirect them to the login page
else:
    header("Location: ../login.php");
endif;
?>
