<?php
// plugins/ram/ram_widget.php
// Gibt das HTML-Markup für das RAM Usage Widget aus.
?>

<div class="ram-usage-widget">
    <h3 class="panel-title"><i class="fa-solid fa-memory"></i> RAM Usage</h3>
    
    <div class="ram-util-bar">
        <span class="util-label">Used / Available:</span>
        <div class="progress-container">
            <div class="progress-bar" id="ramUtilBar" style="width: 0%;"></div>
        </div>
        <span class="util-value" id="ramUtilValue">0.0 %</span>
    </div>

    <div class="ram-details-table">
        <div class="detail-row">
            <span class="detail-label">Total:</span>
            <span class="detail-value" id="ramTotalValue">--</span>
        </div>
        <div class="detail-row">
            <span class="detail-label free">Free:</span>
            <span class="detail-value free" id="ramFreeValue">--</span>
        </div>
        <div class="detail-row">
            <span class="detail-label available">Available:</span>
            <span class="detail-value available" id="ramAvailableValue">--</span>
        </div>
        <div class="detail-row">
            <span class="detail-label cached">Cached:</span>
            <span class="detail-value cached" id="ramCachedValue">--</span>
        </div>
    </div>
    
    <h4 class="swap-title"><i class="fa-solid fa-exchange-alt"></i> Swap Usage</h4>
    <div class="swap-util-bar">
        <div class="progress-container">
            <div class="progress-bar" id="swapUtilBar" style="width: 0%;"></div>
        </div>
        <span class="util-value" id="swapUtilValue">0.0 %</span>
    </div>
    <div class="swap-details-row">
        <span class="swap-label">Used / Total:</span>
        <span class="swap-value" id="swapUsedTotalValue">-- / --</span>
    </div>
</div>
