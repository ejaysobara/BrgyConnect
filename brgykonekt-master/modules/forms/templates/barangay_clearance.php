<div class="form-title">Barangay Clearance</div>
<p style="text-align:center; margin-top:-10px;">(Required under Sec. 152, Par. (c) of RA 7160)</p>

<p style="text-indent: 40px; line-height: 2; margin-top: 28px;">
    This is to certify that <?php echo dfLine("full_name", "long"); ?>, doing business under the
    style and name <?php echo dfLine("business_name", "long"); ?> (if any), is legitimately engaged in the
    business of <?php echo dfLine("business_nature", "long"); ?>, with address at
    <?php echo dfLine("business_address", "long"); ?>.
</p>

<p style="text-indent: 40px; line-height: 2;">
    The applicant has been doing business within the barangay since
    <?php echo dfLine("operating_since", "short"); ?> and is known to the community as peace-loving and
    law-abiding citizen. (Write "Not Applicable" at the blank space in case of first application.)
</p>

<p style="text-indent: 40px; line-height: 2;">
    His/her business establishment does not encroach on any public road or street.
</p>

<p style="text-indent: 40px; line-height: 2;">
    Issued pursuant to Sec. 152, par. (c) of RA 7160 otherwise known as the Local
    Government Code of 1991, this <?php echo dfLine("issue_day", "short"); ?> day of
    <?php echo dfLine("issue_month"); ?>, 20<?php echo dfLine("issue_year", "short"); ?>.
</p>

<div class="sig-block" style="margin-top: 70px;">
    <div class="sig" style="max-width: 45%; margin-right: auto;">
        <?php echo officialLine("secretary"); ?>
        <small>Prepared by: <strong>Barangay Secretary</strong></small>
    </div>
    <div class="sig" style="max-width: 45%; margin-left: auto;">
        <?php echo officialLine("captain"); ?>
        <small><strong>Punong Barangay</strong></small>
    </div>
</div>

<div class="row" style="margin-top: 40px;">
    Cert. Fee: <span class="line short"></span><br>
    O.R. No.: <span class="line short"></span> &nbsp; Collected by: <?php echo officialLine("treasurer"); ?> <small>(Barangay Treasurer)</small><br>
    Issued on: <?php echo dfLine("date_today", "short"); ?>
</div>
