<div class="form-title">Blotter Form</div>

<div class="row" style="text-align:right;">Blotter Entry No.: <span class="line short"></span></div>
<div class="row">Date of Blotter: <?php echo dfLine("date_today"); ?></div>

<div class="row"><strong>A. Complainant (Nagrereklamo)</strong></div>
<div class="row">Name: <?php echo dfLine("full_name", "long"); ?> &nbsp; Age: <?php echo dfLine("age", "short"); ?></div>
<div class="row">Address: <?php echo dfLine("address", "full"); ?></div>
<div class="row">Contact No.: <?php echo dfLine("contact_number"); ?></div>

<div class="row"><strong>B. Respondent (Inirereklamo)</strong></div>
<div class="row">Name: <?php echo dfLine("respondent_name", "long"); ?> &nbsp; Age: <span class="line short"></span></div>
<div class="row">Address: <?php echo dfLine("respondent_address", "full"); ?></div>
<div class="row">Contact No.: <span class="line"></span></div>

<div class="row"><strong>C. Complaint (Reklamo):</strong> <?php echo dfLine("complaint", "long"); ?></div>

<div class="row"><strong>D. Incident Details (Kaganapan ng Pangyayari)</strong></div>
<div class="row">Date: <?php echo dfLine("incident_date"); ?> &nbsp; Time: <?php echo dfLine("incident_time"); ?></div>
<div class="row">Place: <?php echo dfLine("incident_place", "long"); ?></div>

<div class="row"><strong>E. Narrative (Salaysay)</strong></div>
<?php echo dfWriting("narrative", 8); ?>

<div class="row">Signature of Narrator: <span class="line"></span> &nbsp; Date: <span class="line short"></span> &nbsp; Time: <span class="line short"></span></div>

<div class="row"><strong>Witness/es</strong> (may continue at the back)</div>
<div class="row">Name: <span class="line long"></span> &nbsp; Age: <span class="line short"></span></div>
<div class="row">Address: <span class="line long"></span> &nbsp; Contact No.: <span class="line"></span></div>

<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Barangay Desk Officer (Printed Name)</small></div>
    <div class="sig"><span class="line"></span><small>Signature of Barangay Desk Officer</small></div>
</div>
