<div class="form-title">Pet Registration Form</div>

<div class="section-title">Owner Information</div>
<div class="row">Owner Name: <?php echo dfLine("full_name", "long"); ?> &nbsp; Contact No.: <?php echo dfLine("contact_number"); ?></div>
<div class="row">Address: <?php echo dfLine("address", "long"); ?> &nbsp; Email: <?php echo dfLine("email"); ?></div>
<div class="row">Resident Code: <?php echo dfLine("resident_code"); ?></div>

<div class="section-title">Pet Information</div>
<div class="row">
    <?php echo dfCheck("pet_type", "Dog"); ?> Dog &nbsp;&nbsp;
    <?php echo dfCheck("pet_type", "Cat"); ?> Cat &nbsp;&nbsp;
    <?php echo dfCheck("pet_type", "Other"); ?> Other: <?php echo dfLine("pet_type_other", "short"); ?>
    &nbsp;&nbsp;(please attach a photo of the pet to this registration form)
</div>
<div class="row">Pet Name: <?php echo dfLine("pet_name"); ?> &nbsp; Breed: <?php echo dfLine("breed"); ?></div>
<div class="row">Age: <?php echo dfLine("pet_age", "short"); ?> &nbsp; Color: <?php echo dfLine("color"); ?> &nbsp; License #: <span class="line short"></span></div>
<div class="row">Date of latest anti-rabies vaccination: <?php echo dfLine("vaccination_date"); ?></div>
<div class="row">Veterinarian / Clinic: <?php echo dfLine("veterinarian", "long"); ?></div>

<div class="section-title">Emergency Handler</div>
<p>When you are not at home, who can handle your pet?</p>
<div class="row">Name: <?php echo dfLine("handler_name", "long"); ?> &nbsp; Contact No.: <?php echo dfLine("handler_contact"); ?></div>

<div class="section-title">Owner's Acknowledgement</div>
<p style="margin-left: 16px;">
    - All pets must be registered with the barangay.<br>
    - All pets must maintain current anti-rabies vaccination.<br>
    - Pets must be kept on a leash and under the owner's control when outside the owner's premises.<br>
    - Owners are responsible for any injury or damage caused by their pet.
</p>

<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Owner's Signature over Printed Name</small></div>
    <div class="sig"><?php echo dfLine("date_today"); ?><small>Date</small></div>
</div>
<div class="sig-block">
    <div class="sig"><span class="line"></span><small>Received by (Barangay Staff)</small></div>
    <div class="sig"><span class="line"></span><small>Date Received</small></div>
</div>
