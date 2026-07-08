<div class="form-title">Health Center Appointment Form</div>

<div class="row" style="text-align:right;">Queue No.: <span class="line short"></span></div>

<div class="section-title">Patient Information</div>
<div class="row">Full Name: <?php echo dfLine("full_name", "long"); ?> &nbsp; Resident Code: <?php echo dfLine("resident_code"); ?></div>
<div class="row">Birthdate: <?php echo dfLine("birthdate"); ?> &nbsp; Age: <?php echo dfLine("age", "short"); ?> &nbsp; <?php echo dfCheck("gender", "Male"); ?> Male &nbsp; <?php echo dfCheck("gender", "Female"); ?> Female</div>
<div class="row">Address: <?php echo dfLine("address", "full"); ?></div>
<div class="row">Contact No.: <?php echo dfLine("contact_number"); ?> &nbsp; PhilHealth No.: <?php echo dfLine("philhealth_number"); ?></div>

<div class="section-title">Appointment Details</div>
<div class="row">
    <?php echo dfCheck("service_type", "General Checkup"); ?> General Checkup &nbsp;
    <?php echo dfCheck("service_type", "Prenatal"); ?> Prenatal &nbsp;
    <?php echo dfCheck("service_type", "Postnatal"); ?> Postnatal &nbsp;
    <?php echo dfCheck("service_type", "Child Immunization"); ?> Child Immunization<br><br>
    <?php echo dfCheck("service_type", "Senior Checkup"); ?> Senior Checkup &nbsp;
    <?php echo dfCheck("service_type", "PWD Checkup"); ?> PWD Checkup &nbsp;
    <?php echo dfCheck("service_type", "Vaccination"); ?> Vaccination &nbsp;
    <?php echo dfCheck("service_type", "Medical Mission"); ?> Medical Mission<br><br>
    <span class="checkbox"><?php
        // "Other" services (including office visits from Appointment Request)
        $other_service = trim((string)($dform["service_type"] ?? ""));
        $standard = ["General Checkup", "Prenatal", "Postnatal", "Child Immunization", "Senior Checkup", "PWD Checkup", "Vaccination", "Medical Mission", ""];
        $is_other = !in_array($other_service, $standard, true);
        echo $is_other ? '<span style="font-size:11px; line-height:12px; display:block; text-align:center;">&#10005;</span>' : '';
    ?></span> Other: <span class="line long"><?php echo $is_other ? "&nbsp;<strong>" . e($other_service) . "</strong>&nbsp;" : ""; ?></span>
</div>
<div class="row">Preferred Date: <?php echo dfLine("preferred_date"); ?> &nbsp; Preferred Time: <?php echo dfLine("preferred_time"); ?></div>

<div class="section-title">Reason for Visit / Symptoms</div>
<?php echo dfWriting("reason", 3); ?>

<div class="section-title">Relevant Health Information</div>
<div class="row">Allergies: <span class="line long"></span></div>
<div class="row">Existing Conditions: <span class="line long"></span></div>
<div class="row">Current Medications: <span class="line long"></span></div>

<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Patient's Signature over Printed Name</small></div>
    <div class="sig"><span class="line"></span><small>Date</small></div>
</div>
<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Received by (Health Staff)</small></div>
    <div class="sig"><span class="line"></span><small>Schedule Confirmed (Date / Time)</small></div>
</div>
