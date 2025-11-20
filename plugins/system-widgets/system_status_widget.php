<h3 class="panel-title">System Utilization</h3>

<div class="progress-container">
    <div class="progress-bar-wrap">
        <div class="progress-bar" id="cpuProgressBar"><div class="bar"></div><div class="bar-text">CPU</div></div>
        <div class="progress-label">System usage</div>
    </div>
    <div class="progress-bar-wrap">
        <div class="progress-bar" id="ramProgressBar"><div class="bar"></div><div class="bar-text">RAM</div></div>
        <div class="progress-label">RAM usage</div>
    </div>
    <div class="progress-bar-wrap">
        <div class="progress-bar" id="swapProgressBar"><div class="bar"></div><div class="bar-text">SWAP</div></div>
        <div class="progress-label">SWAP usage</div>
    </div>
</div>

<h3 class="panel-title core-title"><i class="fa-solid fa-microchip"></i> Processor Cores</h3>
<div class="core-container" id="coreContainer"></div>

<div class="network-traffic-widget">
    <h3 class="panel-title network-title">
        <i class="fa-solid fa-arrow-right-arrow-left"></i> Live Network Traffic (Gesamt)
    </h3>
    <div class="traffic-display">
        <div id="rxTraffic" class="traffic-item">
            <i class="fa-solid fa-download"></i> 
            <span class="rate-value">0.00 KB/s</span> Receive
        </div>
        <div id="txTraffic" class="traffic-item">
            <i class="fa-solid fa-upload"></i> 
            <span class="rate-value">0.00 KB/s</span> Transmit
        </div>
    </div>
</div>

<h3 class="panel-title gpu-title"><i class="fa-solid fa-gripfire"></i> Nvidia GPU Usage</h3>
<div class="progress-bar-wrap gpu-wrap">
    <div class="progress-bar" id="gpuProgressBar"><div class="bar"></div><div class="bar-text">GPU</div></div>
    <div class="gpu-memory-text">VRAM: <span id="gpuMemoryText">0 MB / 0 MB</span></div>
</div>
