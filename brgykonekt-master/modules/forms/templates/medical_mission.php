<div class="form-title">Medical Mission Registration Form</div>

<div class="row" style="text-align:right;">Registration No.: <span class="line short"></span> &nbsp; Date: <?php echo dfLine("date_today"); ?></div>

<div class="section-title">Participant Information</div>
<div class="row">Full Name: <?php echo dfLine("full_name", "long"); ?></div>
<div class="row">
    Birthdate: <?php echo dfLine("birthdate"); ?> &nbsp;
    Age: <?php echo dfLine("age", "short"); ?> &nbsp;
    <?php echo dfCheck("gender", "Male"); ?> Male &nbsp;
    <?php echo dfCheck("gender", "Female"); ?> Female
</div>
<div class="row">Address: <?php echo dfLine("address", "full"); ?></div>
<div class="row">Contact No.: <?php echo dfLine("contact_number"); ?></div>

<div class="section-title">Services Needed</div>
<div class="row">
    <?php echo dfCheck("services_needed", "General Consultation"); ?> General Consultation &nbsp;
    <?php echo dfCheck("services_needed", "Dental"); ?> Dental &nbsp;
    <?php echo dfCheck("services_needed", "Optical"); ?> Optical &nbsp;
    <?php echo dfCheck("services_needed", "Laboratory"); ?> Laboratory<br><br>
    <?php echo dfCheck("services_needed", "Vaccination"); ?> Vaccination &nbsp;
    <?php echo dfCheck("services_needed", "Circumcision"); ?> Circumcision &nbsp;
    <?php echo dfCheck("services_needed", "Others"); ?> Others: <span class="line long"></span>
</div>

<div class="section-title">Existing Medical Conditions / Maintenance Medicines</div>
<?php echo dfWriting("medical_conditions", 3); ?>

<div class="section-title">For Medical Team Use Only</div>
<div class="row">Blood Pressure: <span class="line short"></span> &nbsp; Weight: <span class="line short"></span> &nbsp; Temperature: <span class="line short"></span></div>
<div class="row">Findings / Prescription:</div>
<div class="writing-lines">
    <span class="line full"></span>
    <span class="line full"></span>
    <span class="line full"></span>
</div>

<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Participant's Signature over Printed Name</small></div>
    <div class="sig"><span class="line"></span><small>Attending Physician / Health Staff</small></div>
</div>
