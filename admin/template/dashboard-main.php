<?php
/**
 * Dashboard Main Template
 *
 * Modern WooCommerce Donations Analytics Dashboard
 *
 * @package    Skyweb_Donation_System
 */

if (!defined('ABSPATH')) {
    exit;
}

$license_manager = skydonate_license();
$is_licensed = $license_manager->is_license_valid();
$features = $license_manager->get_features();
$currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '$';

// Get basic stats
$stats = skydonate_get_dashboard_stats();

// Get additional stats
$gift_aid_total = skydonate_get_gift_aid_total();
$monthly_goal = get_option('skydonate_monthly_goal', 10000);
$goal_progress = $monthly_goal > 0 ? min(100, ($stats['total_amount'] / $monthly_goal) * 100) : 0;
?>

<style>
.skydonate-dashboard-wrap {
    padding: 20px;
    max-width: 1600px;
    margin: 0 auto;
}

.skydonate-dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.skydonate-dashboard-header h1 {
    font-size: 28px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.skydonate-dashboard-header .subtitle {
    color: #64748b;
    margin: 5px 0 0;
}

.header-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.skydonate-select {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    font-size: 14px;
    cursor: pointer;
}

.skydonate-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
}

.skydonate-btn-primary {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
}

.skydonate-btn-primary:hover {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff;
}

.skydonate-btn-outline {
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #475569;
}

.skydonate-btn-outline:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

/* Quick Actions */
.quick-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.quick-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    color: #475569;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.quick-action-btn:hover {
    background: #f8fafc;
    border-color: #6366f1;
    color: #6366f1;
}

.quick-action-btn svg {
    width: 18px;
    height: 18px;
}

/* Stats Grid */
.skydonate-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    align-items: flex-start;
    gap: 16px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-primary .stat-icon { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
.stat-success .stat-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.stat-info .stat-icon { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.stat-warning .stat-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
.stat-purple .stat-icon { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.stat-pink .stat-icon { background: rgba(236, 72, 153, 0.1); color: #ec4899; }

.stat-content {
    flex: 1;
}

.stat-label {
    display: block;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 4px;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.stat-change {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    margin-top: 8px;
    padding: 2px 8px;
    border-radius: 20px;
}

.stat-change.positive { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.stat-change.negative { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.stat-change.neutral { background: rgba(100, 116, 139, 0.1); color: #64748b; }

/* Goal Progress */
.goal-card {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 16px;
    padding: 24px;
    color: #fff;
    margin-bottom: 30px;
}

.goal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.goal-header h3 {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.goal-amount {
    font-size: 14px;
    opacity: 0.9;
}

.goal-progress-bar {
    height: 12px;
    background: rgba(255,255,255,0.3);
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 12px;
}

.goal-progress-fill {
    height: 100%;
    background: #fff;
    border-radius: 6px;
    transition: width 0.5s ease;
}

.goal-stats {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
}

.goal-stats span {
    opacity: 0.9;
}

/* Charts Grid */
.skydonate-charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 1200px) {
    .skydonate-charts-grid {
        grid-template-columns: 1fr;
    }
}

.chart-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.chart-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.chart-legend {
    display: flex;
    gap: 16px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #64748b;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 3px;
}

.legend-color.primary { background: #6366f1; }
.legend-color.secondary { background: #10b981; }
.legend-color.tertiary { background: #f59e0b; }

.chart-body {
    padding: 20px 24px;
    height: 300px;
}

.chart-footer {
    padding: 16px 24px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}

.type-stats {
    display: flex;
    justify-content: space-around;
}

.type-stat {
    text-align: center;
}

.type-label {
    display: block;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 4px;
}

.type-value {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
}

/* Secondary Charts Row */
.skydonate-charts-secondary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 1200px) {
    .skydonate-charts-secondary {
        grid-template-columns: 1fr;
    }
}

.chart-small .chart-body {
    height: 200px;
}

/* Tables Grid */
.skydonate-tables-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 1000px) {
    .skydonate-tables-grid {
        grid-template-columns: 1fr;
    }
}

.table-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.table-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.view-all {
    font-size: 13px;
    color: #6366f1;
    text-decoration: none;
}

.view-all:hover {
    text-decoration: underline;
}

.table-body {
    padding: 0;
}

.skydonate-table {
    width: 100%;
    border-collapse: collapse;
}

.skydonate-table th,
.skydonate-table td {
    padding: 14px 24px;
    text-align: left;
    font-size: 14px;
}

.skydonate-table th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 500;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.skydonate-table tr:not(:last-child) td {
    border-bottom: 1px solid #f1f5f9;
}

.skydonate-table tr:hover td {
    background: #f8fafc;
}

.donor-info, .project-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.donor-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
}

.donor-name, .project-name {
    font-weight: 500;
    color: #1e293b;
}

.amount-cell {
    font-weight: 600;
    color: #10b981;
}

.date-cell {
    color: #64748b;
}

.progress-bar {
    width: 100px;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
    border-radius: 3px;
}

.no-data {
    text-align: center;
    color: #94a3b8;
    padding: 40px !important;
}

/* Upgrade Notice */
.skydonate-upgrade-notice {
    background: linear-gradient(135deg, #1e293b, #334155);
    border-radius: 16px;
    padding: 32px;
    margin-top: 30px;
}

.upgrade-content {
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}

.upgrade-icon {
    width: 64px;
    height: 64px;
    background: rgba(255,255,255,0.1);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fbbf24;
}

.upgrade-text {
    flex: 1;
    min-width: 200px;
}

.upgrade-text h3 {
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 8px;
}

.upgrade-text p {
    color: #94a3b8;
    margin: 0;
    font-size: 14px;
}

/* Activity Feed */
.activity-feed {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-icon.donation { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.activity-icon.recurring { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
.activity-icon.giftaid { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

.activity-content {
    flex: 1;
}

.activity-title {
    font-size: 14px;
    color: #1e293b;
    margin-bottom: 4px;
}

.activity-title strong {
    font-weight: 600;
}

.activity-time {
    font-size: 12px;
    color: #94a3b8;
}

.activity-amount {
    font-size: 16px;
    font-weight: 600;
    color: #10b981;
}
</style>

<div class="skydonate-dashboard-wrap">
    <!-- Header Section -->
    <div class="skydonate-dashboard-header">
        <div class="header-content">
            <h1><?php esc_html_e('Donation Dashboard', 'skydonate'); ?></h1>
            <p class="subtitle"><?php esc_html_e('Monitor your donation performance and insights', 'skydonate'); ?></p>
        </div>
        <div class="header-actions">
            <select id="dashboard-period" class="skydonate-select">
                <option value="7"><?php esc_html_e('Last 7 Days', 'skydonate'); ?></option>
                <option value="30" selected><?php esc_html_e('Last 30 Days', 'skydonate'); ?></option>
                <option value="90"><?php esc_html_e('Last 90 Days', 'skydonate'); ?></option>
                <option value="365"><?php esc_html_e('Last Year', 'skydonate'); ?></option>
            </select>
            <button type="button" class="skydonate-btn skydonate-btn-outline" id="refresh-dashboard">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                <?php esc_html_e('Refresh', 'skydonate'); ?>
            </button>
            <button type="button" class="skydonate-btn skydonate-btn-primary" id="export-donations">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <?php esc_html_e('Export CSV', 'skydonate'); ?>
            </button>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=product')); ?>" class="quick-action-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <?php esc_html_e('New Campaign', 'skydonate'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('edit.php?post_type=shop_order')); ?>" class="quick-action-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            <?php esc_html_e('View Orders', 'skydonate'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=skydonation-settings')); ?>" class="quick-action-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
            <?php esc_html_e('Settings', 'skydonate'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=skydonation-widgets')); ?>" class="quick-action-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <?php esc_html_e('Widgets', 'skydonate'); ?>
        </a>
    </div>

    <!-- Monthly Goal Progress -->
    <div class="goal-card">
        <div class="goal-header">
            <h3><?php esc_html_e('Monthly Fundraising Goal', 'skydonate'); ?></h3>
            <span class="goal-amount"><?php echo esc_html($currency_symbol . number_format($stats['total_amount'], 2)); ?> / <?php echo esc_html($currency_symbol . number_format($monthly_goal, 0)); ?></span>
        </div>
        <div class="goal-progress-bar">
            <div class="goal-progress-fill" style="width: <?php echo esc_attr($goal_progress); ?>%"></div>
        </div>
        <div class="goal-stats">
            <span><?php echo esc_html(round($goal_progress, 1)); ?>% <?php esc_html_e('achieved', 'skydonate'); ?></span>
            <span><?php echo esc_html($currency_symbol . number_format(max(0, $monthly_goal - $stats['total_amount']), 2)); ?> <?php esc_html_e('remaining', 'skydonate'); ?></span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="skydonate-stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label"><?php esc_html_e('Total Donations', 'skydonate'); ?></span>
                <span class="stat-value" id="stat-total"><?php echo esc_html($currency_symbol . number_format($stats['total_amount'], 2)); ?></span>
                <span class="stat-change <?php echo $stats['total_change'] >= 0 ? 'positive' : 'negative'; ?>" id="stat-total-change">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <path d="<?php echo $stats['total_change'] >= 0 ? 'M7 14l5-5 5 5z' : 'M7 10l5 5 5-5z'; ?>"/>
                    </svg>
                    <span><?php echo esc_html(abs($stats['total_change'])); ?>%</span>
                </span>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label"><?php esc_html_e('Total Donors', 'skydonate'); ?></span>
                <span class="stat-value" id="stat-donors"><?php echo esc_html(number_format($stats['unique_donors'])); ?></span>
                <span class="stat-change <?php echo $stats['donors_change'] >= 0 ? 'positive' : 'negative'; ?>" id="stat-donors-change">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <path d="<?php echo $stats['donors_change'] >= 0 ? 'M7 14l5-5 5 5z' : 'M7 10l5 5 5-5z'; ?>"/>
                    </svg>
                    <span><?php echo esc_html(abs($stats['donors_change'])); ?>%</span>
                </span>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                    <line x1="1" y1="10" x2="23" y2="10"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label"><?php esc_html_e('Donations Count', 'skydonate'); ?></span>
                <span class="stat-value" id="stat-count"><?php echo esc_html(number_format($stats['order_count'])); ?></span>
                <span class="stat-change neutral">
                    <span><?php esc_html_e('this period', 'skydonate'); ?></span>
                </span>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label"><?php esc_html_e('Average Donation', 'skydonate'); ?></span>
                <span class="stat-value" id="stat-avg"><?php echo esc_html($currency_symbol . number_format($stats['average_donation'], 2)); ?></span>
                <span class="stat-change neutral">
                    <span><?php esc_html_e('per donation', 'skydonate'); ?></span>
                </span>
            </div>
        </div>

        <div class="stat-card stat-purple">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label"><?php esc_html_e('Recurring Revenue', 'skydonate'); ?></span>
                <span class="stat-value"><?php echo esc_html($currency_symbol . number_format($stats['recurring_total'], 2)); ?></span>
                <span class="stat-change neutral">
                    <span><?php esc_html_e('monthly', 'skydonate'); ?></span>
                </span>
            </div>
        </div>

        <div class="stat-card stat-pink">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label"><?php esc_html_e('Gift Aid Collected', 'skydonate'); ?></span>
                <span class="stat-value"><?php echo esc_html($currency_symbol . number_format($gift_aid_total, 2)); ?></span>
                <span class="stat-change positive">
                    <span><?php esc_html_e('+25% tax relief', 'skydonate'); ?></span>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Charts Grid -->
    <div class="skydonate-charts-grid">
        <!-- Donations Trend Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><?php esc_html_e('Donation Trends', 'skydonate'); ?></h3>
                <div class="chart-legend">
                    <span class="legend-item"><span class="legend-color primary"></span><?php esc_html_e('Amount', 'skydonate'); ?></span>
                    <span class="legend-item"><span class="legend-color secondary"></span><?php esc_html_e('Count', 'skydonate'); ?></span>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="donations-trend-chart"></canvas>
            </div>
        </div>

        <!-- Donation Type Breakdown -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><?php esc_html_e('Donation Types', 'skydonate'); ?></h3>
            </div>
            <div class="chart-body">
                <canvas id="donation-types-chart"></canvas>
            </div>
            <div class="chart-footer">
                <div class="type-stats">
                    <div class="type-stat">
                        <span class="type-label"><?php esc_html_e('One-time', 'skydonate'); ?></span>
                        <span class="type-value"><?php echo esc_html($currency_symbol . number_format($stats['one_time_total'], 2)); ?></span>
                    </div>
                    <div class="type-stat">
                        <span class="type-label"><?php esc_html_e('Recurring', 'skydonate'); ?></span>
                        <span class="type-value"><?php echo esc_html($currency_symbol . number_format($stats['recurring_total'], 2)); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Charts -->
    <div class="skydonate-charts-secondary">
        <!-- Payment Methods Chart -->
        <div class="chart-card chart-small">
            <div class="chart-header">
                <h3><?php esc_html_e('Payment Methods', 'skydonate'); ?></h3>
            </div>
            <div class="chart-body">
                <canvas id="payment-methods-chart"></canvas>
            </div>
        </div>

        <!-- Top Campaigns Chart -->
        <div class="chart-card chart-small">
            <div class="chart-header">
                <h3><?php esc_html_e('Top Campaigns', 'skydonate'); ?></h3>
            </div>
            <div class="chart-body">
                <canvas id="top-campaigns-chart"></canvas>
            </div>
        </div>

        <!-- Donation Amounts Distribution -->
        <div class="chart-card chart-small">
            <div class="chart-header">
                <h3><?php esc_html_e('Amount Distribution', 'skydonate'); ?></h3>
            </div>
            <div class="chart-body">
                <canvas id="amounts-distribution-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <div class="skydonate-tables-grid">
        <!-- Top Projects -->
        <div class="table-card">
            <div class="table-header">
                <h3><?php esc_html_e('Top Campaigns', 'skydonate'); ?></h3>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=product')); ?>" class="view-all"><?php esc_html_e('View All', 'skydonate'); ?></a>
            </div>
            <div class="table-body">
                <table class="skydonate-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Campaign', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Donations', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Total', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Progress', 'skydonate'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="top-projects-list">
                        <?php if (!empty($stats['top_projects'])): ?>
                            <?php foreach ($stats['top_projects'] as $project): ?>
                            <tr>
                                <td>
                                    <div class="project-info">
                                        <span class="project-name"><?php echo esc_html($project['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo esc_html(number_format($project['count'])); ?></td>
                                <td class="amount-cell"><?php echo esc_html($currency_symbol . number_format($project['total'], 2)); ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo esc_attr(min(100, ($project['total'] / max(1, $stats['total_amount'])) * 100)); ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data"><?php esc_html_e('No campaigns found', 'skydonate'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Donations -->
        <div class="table-card">
            <div class="table-header">
                <h3><?php esc_html_e('Recent Donations', 'skydonate'); ?></h3>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=shop_order')); ?>" class="view-all"><?php esc_html_e('View All', 'skydonate'); ?></a>
            </div>
            <div class="table-body">
                <table class="skydonate-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Donor', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Campaign', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Amount', 'skydonate'); ?></th>
                            <th><?php esc_html_e('Date', 'skydonate'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="recent-donations-list">
                        <?php if (!empty($stats['recent_donations'])): ?>
                            <?php foreach ($stats['recent_donations'] as $donation): ?>
                            <tr>
                                <td>
                                    <div class="donor-info">
                                        <div class="donor-avatar"><?php echo esc_html(strtoupper(substr($donation['name'], 0, 1))); ?></div>
                                        <span class="donor-name"><?php echo esc_html($donation['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo esc_html($donation['project']); ?></td>
                                <td class="amount-cell"><?php echo esc_html($currency_symbol . number_format($donation['amount'], 2)); ?></td>
                                <td class="date-cell"><?php echo esc_html(human_time_diff(strtotime($donation['date']), current_time('timestamp')) . ' ago'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data"><?php esc_html_e('No recent donations', 'skydonate'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Donors Table -->
    <div class="table-card" style="margin-bottom: 30px;">
        <div class="table-header">
            <h3><?php esc_html_e('Top Donors', 'skydonate'); ?></h3>
        </div>
        <div class="table-body">
            <table class="skydonate-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Donor', 'skydonate'); ?></th>
                        <th><?php esc_html_e('Total Donated', 'skydonate'); ?></th>
                        <th><?php esc_html_e('Donations', 'skydonate'); ?></th>
                        <th><?php esc_html_e('Last Donation', 'skydonate'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $top_donors = isset($stats['top_donors']) ? $stats['top_donors'] : array();
                    if (!empty($top_donors)):
                        foreach ($top_donors as $donor): ?>
                        <tr>
                            <td>
                                <div class="donor-info">
                                    <div class="donor-avatar"><?php echo esc_html(strtoupper(substr($donor['name'], 0, 1))); ?></div>
                                    <span class="donor-name"><?php echo esc_html($donor['name']); ?></span>
                                </div>
                            </td>
                            <td class="amount-cell"><?php echo esc_html($currency_symbol . number_format($donor['total'], 2)); ?></td>
                            <td><?php echo esc_html($donor['count']); ?></td>
                            <td class="date-cell"><?php echo esc_html(human_time_diff(strtotime($donor['last_date']), current_time('timestamp')) . ' ago'); ?></td>
                        </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="4" class="no-data"><?php esc_html_e('No donor data available', 'skydonate'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!$is_licensed): ?>
    <!-- Upgrade Notice -->
    <div class="skydonate-upgrade-notice">
        <div class="upgrade-content">
            <div class="upgrade-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </div>
            <div class="upgrade-text">
                <h3><?php esc_html_e('Unlock Advanced Analytics', 'skydonate'); ?></h3>
                <p><?php esc_html_e('Get detailed donor insights, export capabilities, and AI-powered recommendations with a Pro license.', 'skydonate'); ?></p>
            </div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=skydonation-licenses')); ?>" class="skydonate-btn skydonate-btn-primary">
                <?php esc_html_e('Activate License', 'skydonate'); ?>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize charts
    if (typeof Chart !== 'undefined') {
        initDashboardCharts();
    }

    // Period selector
    $('#dashboard-period').on('change', function() {
        refreshDashboardData($(this).val());
    });

    // Refresh button
    $('#refresh-dashboard').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true);
        refreshDashboardData($('#dashboard-period').val());
        setTimeout(function() {
            $btn.prop('disabled', false);
        }, 2000);
    });

    // Export button
    $('#export-donations').on('click', function() {
        exportDonations();
    });
});

function initDashboardCharts() {
    // Chart defaults
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';

    // Donations Trend Chart
    var trendCtx = document.getElementById('donations-trend-chart');
    if (trendCtx) {
        new Chart(trendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?php echo wp_json_encode(array_column($stats['daily_data'], 'date')); ?>,
                datasets: [{
                    label: '<?php esc_html_e('Amount', 'skydonate'); ?>',
                    data: <?php echo wp_json_encode(array_column($stats['daily_data'], 'total')); ?>,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                }, {
                    label: '<?php esc_html_e('Count', 'skydonate'); ?>',
                    data: <?php echo wp_json_encode(array_column($stats['daily_data'], 'count')); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    yAxisID: 'y1',
                    tension: 0.4,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        grid: { display: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Donation Types Doughnut Chart
    var typesCtx = document.getElementById('donation-types-chart');
    if (typesCtx) {
        new Chart(typesCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['<?php esc_html_e('One-time', 'skydonate'); ?>', '<?php esc_html_e('Recurring', 'skydonate'); ?>'],
                datasets: [{
                    data: [<?php echo floatval($stats['one_time_total']); ?>, <?php echo floatval($stats['recurring_total']); ?>],
                    backgroundColor: ['#6366f1', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // Payment Methods Chart
    var paymentCtx = document.getElementById('payment-methods-chart');
    if (paymentCtx) {
        new Chart(paymentCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['<?php esc_html_e('Card', 'skydonate'); ?>', '<?php esc_html_e('PayPal', 'skydonate'); ?>', '<?php esc_html_e('Bank', 'skydonate'); ?>', '<?php esc_html_e('Other', 'skydonate'); ?>'],
                datasets: [{
                    data: <?php
                        $payment_data = isset($stats['payment_methods']) ? array_values($stats['payment_methods']) : [60, 25, 10, 5];
                        echo wp_json_encode($payment_data);
                    ?>,
                    backgroundColor: ['#6366f1', '#f59e0b', '#10b981', '#94a3b8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 8, font: { size: 11 } }
                    }
                }
            }
        });
    }

    // Top Campaigns Horizontal Bar Chart
    var campaignsCtx = document.getElementById('top-campaigns-chart');
    if (campaignsCtx) {
        var topProjects = <?php echo wp_json_encode(array_slice($stats['top_projects'], 0, 5)); ?>;
        new Chart(campaignsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: topProjects.map(function(p) { return p.name.substring(0, 15) + (p.name.length > 15 ? '...' : ''); }),
                datasets: [{
                    data: topProjects.map(function(p) { return p.total; }),
                    backgroundColor: ['#6366f1', '#8b5cf6', '#a855f7', '#c084fc', '#d8b4fe'],
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // Amount Distribution Chart
    var amountsCtx = document.getElementById('amounts-distribution-chart');
    if (amountsCtx) {
        new Chart(amountsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['<?php esc_html_e('$1-25', 'skydonate'); ?>', '<?php esc_html_e('$26-50', 'skydonate'); ?>', '<?php esc_html_e('$51-100', 'skydonate'); ?>', '<?php esc_html_e('$101-250', 'skydonate'); ?>', '<?php esc_html_e('$250+', 'skydonate'); ?>'],
                datasets: [{
                    data: <?php
                        $amount_dist = isset($stats['amount_distribution']) ? array_values($stats['amount_distribution']) : [30, 25, 20, 15, 10];
                        echo wp_json_encode($amount_dist);
                    ?>,
                    backgroundColor: '#6366f1',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true }
                }
            }
        });
    }
}

function refreshDashboardData(period) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'skydonate_get_dashboard_stats',
            period: period,
            nonce: '<?php echo wp_create_nonce('skydonate_dashboard_nonce'); ?>'
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            }
        }
    });
}

function exportDonations() {
    var period = jQuery('#dashboard-period').val();
    window.location.href = ajaxurl + '?action=skydonate_export_donations&period=' + period + '&nonce=<?php echo wp_create_nonce('skydonate_export_nonce'); ?>';
}
</script>
