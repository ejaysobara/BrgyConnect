<?php
// Shared partial: lists printable blank forms (registry in includes/forms.php).
require_once dirname(__DIR__, 2) . "/includes/forms.php";

$printable_groups = [];
foreach (getPrintableForms() as $form_key => $form_def) {
    if (staffLevel() < $form_def[2]) {
        continue;
    }
    $printable_groups[$form_def[3]][$form_key] = $form_def;
}
?>
<?php if (empty($printable_groups)) { ?>
    <p class="empty-state">No printable forms available yet.</p>
<?php } else { ?>
    <?php foreach ($printable_groups as $group_name => $group_forms) { ?>
        <h4><?php echo e($group_name); ?></h4>
        <div class="table-wrap">
            <table>
                <tbody>
                    <?php foreach ($group_forms as $form_key => $form_def) { ?>
                        <tr>
                            <td><?php echo e($form_def[0]); ?></td>
                            <td style="text-align:right;">
                                <a class="button secondary" href="<?php echo e(appPath("modules/forms/print.php?form=" . $form_key)); ?>" target="_blank">Print / Save as PDF</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
<?php } ?>
