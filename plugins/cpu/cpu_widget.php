<?php
// plugins/cpu/cpu_widget.php
// Gibt das HTML-Markup für das CPU-Widget aus.
?>

<div class="cpu-usage-widget">
    <h3 class="panel-title"><i class="fa-solid fa-microchip"></i> CPU Usage</h3>
    
    <div class="overall-cpu">
        <span class="overall-label">Overall:</span>
        <span class="overall-value" id="cpuOverallValue">--- %</span>
    </div>
    
    <div class="cores-container" id="cpuCoreContainer">
        <span class="loading-message">Loading core data...</span>
    </div>
</div>
