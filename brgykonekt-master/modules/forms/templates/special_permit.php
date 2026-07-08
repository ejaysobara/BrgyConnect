<div class="form-title">Request for Special Permit</div>

<div class="row" style="text-align:right;">Date: <?php echo dfLine("date_today"); ?></div>

<div class="section-title">Purpose</div>
<div class="row">
    <?php echo dfCheck("purpose_type", "Community Event"); ?> Community Event &nbsp;&nbsp;
    <?php echo dfCheck("purpose_type", "Construction / Repair"); ?> Construction / Repair &nbsp;&nbsp;
    <?php echo dfCheck("purpose_type", "Transport / Carry Cargo"); ?> Transport / Carry Cargo<br><br>
    <?php echo dfCheck("purpose_type", "Fundraising Activity"); ?> Fundraising Activity &nbsp;&nbsp;
    <?php echo dfCheck("purpose_type", "Use of Barangay Facility"); ?> Use of Barangay Facility &nbsp;&nbsp;
    <?php echo dfCheck("purpose_type", "Others"); ?> Others (specify): <?php echo dfLine("purpose_other", "long"); ?>
</div>

<div class="section-title">Applicant Details</div>
<div class="row">Name of Applicant / Organization: <?php echo dfLine("full_name", "long"); ?></div>
<div class="row">Address: <?php echo dfLine("address", "full"); ?></div>
<div class="row">Contact No.: <?php echo dfLine("contact_number"); ?> &nbsp; Email: <?php echo dfLine("email"); ?></div>

<div class="section-title">Details of the Request</div>
<div class="row">Description of activity / unit / equipment to be used:</div>
<?php echo dfWriting("description", 3); ?>
<div class="row">Location / Venue: <?php echo dfLine("venue", "long"); ?></div>
<div class="row">Date Covered: From <?php echo dfLine("date_from"); ?> To <?php echo dfLine("date_to"); ?></div>
<div class="row">Expected number of participants (if applicable): <?php echo dfLine("participants", "short"); ?></div>

<div class="section-title">Requirements (arrange in the following order)</div>
<div class="row">
    <span class="checkbox"></span> 1. Valid ID of the applicant<br>
    <span class="checkbox"></span> 2. Proof of residency or barangay clearance<br>
    <span class="checkbox"></span> 3. Letter of intent / activity plan<br>
    <span class="checkbox"></span> 4. Consent of affected neighbors (if applicable)<br>
    <span class="checkbox"></span> 5. Other supporting documents
</div>

<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Requested by: Print Name and Signature<br>(Applicant / Authorized Representative)</small></div>
</div>

<div class="row" style="margin-top:24px;">Remarks:</div>
<div class="writing-lines">
    <span class="line full"></span>
    <span class="line full"></span>
</div>

<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Evaluated by: Print Name and Signature</small></div>
</div>
