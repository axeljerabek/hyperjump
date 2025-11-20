<?php
// plugins/nvidia/nvidia_widget.php
// Gibt das HTML-Markup für das Widget aus.
?>

<div class="nvidia-gpu-widget">
    <h3 class="panel-title"><i class="fa-solid fa-microchip"></i> NVIDIA GPU Usage</h3>
    
    <div class="gpu-display-container">
        
        <div class="gpu-util-bar">
            <span class="util-label">GPU Load:</span>
            <div class="progress-container">
                <div class="progress-bar" id="gpuUtilBar" style="width: 0%;"></div>
            </div>
            <span class="util-value" id="gpuUtilValue">0.00%</span>
        </div>
        
        <div class="gpu-memory-bar">
            <span class="memory-bar-label">Memory Load:</span>
            <div class="progress-container">
                <div class="progress-bar" id="gpuMemBar" style="width: 0%;"></div>
            </div>
            <span class="memory-bar-value" id="gpuMemBarValue">0.00%</span>
        </div>

        <div class="gpu-memory-info">
            <span class="memory-label">Memory:</span>
            <span class="memory-value" id="gpuMemoryValue">0 MiB / 0 MiB</span>
        </div>

    </div>
</div>
