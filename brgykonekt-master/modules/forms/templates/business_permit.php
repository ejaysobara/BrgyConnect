<div class="form-title">Application for Business Permit</div>

<div class="two-col">
    <div>
        <div class="row">Tax Year: <span class="line short"><?php echo empty($dform["application_type"]) ? "" : "<strong>" . e(date("Y")) . "</strong>"; ?></span></div>
        <div class="row">Date of Application: <?php echo dfLine("date_today"); ?></div>
        <div class="row"><?php echo dfCheck("application_type", "New"); ?> New &nbsp;&nbsp; <?php echo dfCheck("application_type", "Renewal"); ?> Renewal</div>
    </div>
    <div>
        <div class="row">Official Receipt No.: <span class="line"></span></div>
        <div class="row">O.R. Date: <span class="line"></span></div>
        <div class="row">Amount Paid: <span class="line"></span></div>
    </div>
</div>

<div class="section-title">Form of Organization</div>
<div class="row">
    <?php echo dfCheck("organization_type", "Single (Sole Proprietorship)"); ?> Single (Sole Proprietorship) &nbsp;
    <?php echo dfCheck("organization_type", "Partnership"); ?> Partnership &nbsp;
    <?php echo dfCheck("organization_type", "Corporation"); ?> Corporation &nbsp;
    <?php echo dfCheck("organization_type", "Cooperative"); ?> Cooperative
</div>

<div class="section-title">Owner / Taxpayer Information</div>
<div class="row">Last Name: <?php echo dfLine("last_name"); ?> &nbsp; First Name: <?php echo dfLine("first_name"); ?> &nbsp; Middle Name: <?php echo dfLine("middle_name"); ?></div>
<div class="row">Home Address: <?php echo dfLine("home_address", "full"); ?></div>
<div class="row">Telephone / Mobile No.: <?php echo dfLine("contact_number"); ?> &nbsp; Email Address: <?php echo dfLine("email"); ?></div>
<div class="row">Citizenship: <?php echo dfLine("citizenship"); ?> &nbsp; <?php echo dfCheck("gender", "Male"); ?> Male &nbsp; <?php echo dfCheck("gender", "Female"); ?> Female</div>

<div class="section-title">Business Information</div>
<div class="row">Business / Trade Name: <?php echo dfLine("business_name", "long"); ?></div>
<div class="row">Business Address: <?php echo dfLine("business_address", "full"); ?></div>
<div class="row">Main Line of Business: <?php echo dfLine("line_of_business", "long"); ?></div>
<div class="row">Main Products / Services: <?php echo dfLine("products", "long"); ?></div>
<div class="row">No. of Employees: <?php echo dfLine("employees", "short"); ?> &nbsp; Capital: <?php echo dfLine("capital"); ?></div>
<div class="row">DTI / SEC / CDA Registration No.: <?php echo dfLine("registration_no"); ?> &nbsp; Date Issued: <?php echo dfLine("registration_date", "short"); ?></div>
<div class="row">Ownership of Premises: <?php echo dfCheck("premises", "Owned"); ?> Owned &nbsp; <?php echo dfCheck("premises", "Leased"); ?> Leased &nbsp; — Lessor's Name: <?php echo dfLine("lessor"); ?> &nbsp; Rent per Month: <?php echo dfLine("rent", "short"); ?></div>

<div class="section-title">Documentary Requirements</div>
<div class="row">
    <span class="checkbox"></span> Proof of Business Registration (DTI / SEC / CDA)<br>
    <span class="checkbox"></span> Contract of Lease / Notice of Award (if premises are leased or rented)<br>
    <span class="checkbox"></span> Sanitary Permit<br>
    <span class="checkbox"></span> Fire and Safety Inspection Certificate<br>
    <span class="checkbox"></span> Occupancy Permit
</div>

<p style="margin-top: 20px;">I declare under penalty of perjury that all information in this application is true and correct based on my personal knowledge and authentic records submitted to the barangay.</p>

<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Signature of Applicant over Printed Name</small></div>
    <div class="sig"><span class="line"></span><small>Position / Title</small></div>
</div>
<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Received / Evaluated by (Barangay Staff)</small></div>
    <div class="sig"><span class="line"></span><small>Date</small></div>
</div>
