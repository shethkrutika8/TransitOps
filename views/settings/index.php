<?php
// TransitOps Settings Module View
if (!defined('SECURE_ACCESS')) exit;
require_once dirname(dirname(__DIR__)) . '/helpers/AuthHelper.php';
$isManager = AuthHelper::hasPermission('settings_manage');
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-0">System Settings</h3>
        <p class="text-secondary small mb-0">Configure application-wide preferences and thresholds</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if ($isManager): ?>
        <button type="submit" form="settingsForm" class="btn btn-primary">
            <i class="fas fa-save me-2"></i> Save Changes
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Settings Form -->
<div class="premium-card">
    <form id="settingsForm" action="<?php echo BASE_URL; ?>/settings/update" method="POST">
        <div class="row g-4">
            <!-- General Settings -->
            <div class="col-12">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="fas fa-cog me-2"></i>General Configuration
                </h5>
            </div>
            
            <div class="col-md-6">
                <label for="app_name" class="form-label">Application Name</label>
                <input type="text" class="form-control" id="app_name" name="app_name" 
                       value="<?php echo htmlspecialchars($settings['app_name'] ?? 'TransitOps'); ?>" maxlength="100" required
                       <?php echo $isManager ? '' : 'readonly'; ?>>
            </div>
            
            <div class="col-md-6">
                <label for="company_name" class="form-label">Company Name</label>
                <input type="text" class="form-control" id="company_name" name="company_name" 
                       value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>" maxlength="150" required
                       <?php echo $isManager ? '' : 'readonly'; ?>>
            </div>
            
            <div class="col-md-6">
                <label for="currency" class="form-label">Currency Symbol</label>
                <input type="text" class="form-control" id="currency" name="currency" 
                       value="<?php echo htmlspecialchars($settings['currency'] ?? '$'); ?>" maxlength="8" required
                       <?php echo $isManager ? '' : 'readonly'; ?>>
            </div>
            
            <div class="col-md-6">
                <label for="timezone" class="form-label">Timezone</label>
                <select class="form-select" id="timezone" name="timezone" required
                        <?php echo $isManager ? '' : 'disabled'; ?>>
                    <option value="UTC" <?php echo ($settings['timezone'] ?? '') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                    <option value="America/New_York" <?php echo ($settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : ''; ?>>America/New_York</option>
                    <option value="America/Chicago" <?php echo ($settings['timezone'] ?? '') === 'America/Chicago' ? 'selected' : ''; ?>>America/Chicago</option>
                    <option value="America/Denver" <?php echo ($settings['timezone'] ?? '') === 'America/Denver' ? 'selected' : ''; ?>>America/Denver</option>
                    <option value="America/Los_Angeles" <?php echo ($settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : ''; ?>>America/Los_Angeles</option>
                    <option value="Europe/London" <?php echo ($settings['timezone'] ?? '') === 'Europe/London' ? 'selected' : ''; ?>>Europe/London</option>
                    <option value="Europe/Paris" <?php echo ($settings['timezone'] ?? '') === 'Europe/Paris' ? 'selected' : ''; ?>>Europe/Paris</option>
                    <option value="Asia/Kolkata" <?php echo ($settings['timezone'] ?? '') === 'Asia/Kolkata' ? 'selected' : ''; ?>>Asia/Kolkata</option>
                    <option value="Asia/Tokyo" <?php echo ($settings['timezone'] ?? '') === 'Asia/Tokyo' ? 'selected' : ''; ?>>Asia/Tokyo</option>
                    <option value="Australia/Sydney" <?php echo ($settings['timezone'] ?? '') === 'Australia/Sydney' ? 'selected' : ''; ?>>Australia/Sydney</option>
                </select>
            </div>
            
            <!-- Warning Thresholds -->
            <div class="col-12 mt-4">
                <h5 class="fw-bold text-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>Warning Thresholds
                </h5>
            </div>
            
            <div class="col-md-6">
                <label for="license_warning_days" class="form-label">License Expiry Warning (Days)</label>
                <input type="number" class="form-control" id="license_warning_days" name="license_warning_days" 
                       value="<?php echo htmlspecialchars($settings['license_warning_days'] ?? '30'); ?>" min="7" max="365"
                       <?php echo $isManager ? 'required' : 'readonly'; ?>>
                <div class="form-text">Alert when driver license is expiring within this period</div>
            </div>
            
            <div class="col-md-6">
                <label for="insurance_warning_days" class="form-label">Insurance Expiry Warning (Days)</label>
                <input type="number" class="form-control" id="insurance_warning_days" name="insurance_warning_days" 
                       value="<?php echo htmlspecialchars($settings['insurance_warning_days'] ?? '30'); ?>" min="7" max="365"
                       <?php echo $isManager ? 'required' : 'readonly'; ?>>
                <div class="form-text">Alert when vehicle insurance is expiring within this period</div>
            </div>
        </div>
    </form>
</div>

<?php if (!$isManager): ?>
<div class="alert alert-info mt-3">
    <i class="fas fa-info-circle me-2"></i>
    You are in read-only mode. Contact a manager to modify system settings.
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($isManager): ?>
    AjaxOps.submitForm(document.getElementById('settingsForm'));
    <?php endif; ?>
});
</script>
