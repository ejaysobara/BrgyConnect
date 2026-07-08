<div class="form-title">Relief Assistance Form</div>

<div class="row" style="text-align:right;">Control No.: <span class="line short"></span> &nbsp; Date: <?php echo dfLine("date_today"); ?></div>

<div class="section-title">Applicant Information</div>
<div class="row">Head of Family / Applicant: <?php echo dfLine("full_name", "long"); ?> &nbsp; Age: <?php echo dfLine("age", "short"); ?></div>
<div class="row">Address: <?php echo dfLine("address", "full"); ?></div>
<div class="row">Purok: <?php echo dfLine("purok", "short"); ?> &nbsp; Contact No.: <?php echo dfLine("contact_number"); ?></div>
<div class="row">Number of Household Members: <?php echo dfLine("household_members", "short"); ?></div>

<div class="section-title">Assistance Requested</div>
<div class="row">
    <?php echo dfCheck("assistance_type", "Food Pack"); ?> Food Pack &nbsp;
    <?php echo dfCheck("assistance_type", "Financial Assistance"); ?> Financial Assistance &nbsp;
    <?php echo dfCheck("assistance_type", "Medical Assistance"); ?> Medical Assistance<br><br>
    <?php echo dfCheck("assistance_type", "Shelter Materials"); ?> Shelter Materials &nbsp;
    <?php echo dfCheck("assistance_type", "Evacuation Support"); ?> Evacuation Support &nbsp;
    <?php echo dfCheck("assistance_type", "Others"); ?> Others: <span class="line long"></span>
</div>
<div class="row">Calamity / Reason for Assistance: <?php echo dfLine("calamity", "long"); ?></div>

<div class="section-title">Additional Details</div>
<?php echo dfWriting("details", 4); ?>

<div class="section-title">For Barangay Use Only</div>
<div class="row">
    <span class="checkbox"></span> Verified indigent / affected household &nbsp;
    <span class="checkbox"></span> Endorsed to MSWDO / CSWDO &nbsp;
    <span class="checkbox"></span> Released
</div>
<div class="row">Items / Amount Released: <span class="line long"></span> &nbsp; Date Released: <span class="line short"></span></div>

<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Applicant's Signature over Printed Name</small></div>
    <div class="sig"><span class="line"></span><small>Punong Barangay / Authorized Official</small></div>
</div>
