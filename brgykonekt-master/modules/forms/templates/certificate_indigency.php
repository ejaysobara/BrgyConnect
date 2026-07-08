<div class="form-title" style="text-decoration: underline;">Certificate of Indigency</div>

<div class="row" style="text-align:right;">Date: <?php echo dfLine("date_today"); ?></div>

<p style="margin-top: 28px;">To whom it may concern:</p>

<p style="text-indent: 40px; line-height: 2;">
    This is to certify that <?php echo dfLine("full_name", "long"); ?>,
    <?php echo dfLine("age", "short"); ?> years old, is a bonafide resident and considered
    <strong>an indigent member of our community</strong> based on our records. He/she meets
    the criteria set forth by our Barangay in terms of income and livelihood.
</p>

<p style="text-indent: 40px; line-height: 2;">
    This certificate is issued upon request for <?php echo dfLine("purpose", "long"); ?>.
</p>

<p style="text-indent: 40px; line-height: 2;">
    Should you need further verification or information, please do not hesitate to contact
    our Barangay Office.
</p>

<div class="sig-block" style="margin-top: 80px;">
    <div class="sig" style="max-width: 45%; margin-right: auto;">
        <?php echo officialLine("secretary"); ?>
        <small>Prepared by: <strong>Barangay Secretary</strong></small>
    </div>
    <div class="sig" style="max-width: 45%; margin-left: auto;">
        <?php echo officialLine("captain"); ?>
        <small><strong>Punong Barangay</strong></small>
    </div>
</div>
