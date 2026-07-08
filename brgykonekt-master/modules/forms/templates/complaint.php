<div class="form-title">Complaint</div>

<div class="two-col">
    <div>
        <?php echo dfWriting("complainants", 2); ?>
        <p style="text-align:center;"><strong>Complainant/s</strong></p>
        <p style="text-align:center;">- against -</p>
        <?php echo dfWriting("respondents", 2); ?>
        <p style="text-align:center;"><strong>Respondent/s</strong></p>
    </div>
    <div>
        <div class="row">Barangay Case No.: <span class="line"></span></div>
        <div class="row">For: <?php echo dfLine("case_for", "long"); ?></div>
    </div>
</div>

<p>I/WE hereby complain against the above named respondent/s for violating my/our rights and interests in the following manner:</p>
<?php echo dfWriting("complaint_details", 4); ?>

<p>THEREFORE, I/WE pray that the following relief/s be granted to me/us in accordance with law and/or equity:</p>
<?php echo dfWriting("relief", 3); ?>

<div class="row">Made this <?php echo dfLine("issue_day", "short"); ?> day of <?php echo dfLine("issue_month"); ?>, 20<?php echo dfLine("issue_year", "short"); ?>.</div>

<div class="sig-block">
    <div class="sig" style="max-width: 45%; margin-left: auto;"><span class="line"></span><small>Complainant/s</small></div>
</div>

<div class="row" style="margin-top: 32px;">Received and filed this <span class="line short"></span> day of <span class="line"></span>, 20<span class="line short" style="min-width:40px;"></span>.</div>

<div class="sig-block">
    <div class="sig" style="max-width: 45%; margin-left: auto;"><span class="line"></span><small>Barangay Desk Officer</small></div>
</div>
