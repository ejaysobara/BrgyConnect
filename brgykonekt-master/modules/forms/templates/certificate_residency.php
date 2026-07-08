<div class="form-title" style="text-decoration: underline;">Certificate of Residency</div>

<div class="row" style="text-align:right;">Date: <?php echo dfLine("date_today"); ?></div>

<p style="margin-top: 28px;">To whom it may concern:</p>

<p style="text-indent: 40px; line-height: 2;">
    This is to certify that <?php echo dfLine("full_name", "long"); ?>,
    <?php echo dfLine("age", "short"); ?> years of age,
    <?php echo dfLine("civil_status", "short"); ?>, Filipino, is a <strong>bonafide resident</strong>
    of this Barangay with residence address at <?php echo dfLine("address", "long"); ?>,
    and has been residing in this Barangay for <?php echo dfLine("years_of_residency", "short"); ?> year(s).
</p>

<p style="text-indent: 40px; line-height: 2;">
    This certification is issued upon the request of the above-named person for
    <?php echo dfLine("purpose", "long"); ?> and for whatever legal purpose it may serve.
</p>

<p style="text-indent: 40px; line-height: 2;">
    Issued this <?php echo dfLine("issue_day", "short"); ?> day of
    <?php echo dfLine("issue_month"); ?>, 20<?php echo dfLine("issue_year", "short"); ?> at the
    Office of the Punong Barangay.
</p>

<div class="sig-block" style="margin-top: 70px;">
    <div class="sig" style="max-width: 45%;">
        <span class="line"></span>
        <small><strong>Signature of Applicant</strong></small>
    </div>
    <div class="sig" style="max-width: 45%;">
        <?php echo officialLine("captain"); ?>
        <small><strong>Punong Barangay</strong></small>
    </div>
</div>

<div class="sig-block" style="margin-top: 40px;">
    <div class="sig" style="max-width: 45%; margin-right: auto;">
        <?php echo officialLine("secretary"); ?>
        <small>Prepared by: <strong>Barangay Secretary</strong></small>
    </div>
</div>
