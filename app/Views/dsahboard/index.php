<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Map Sumatera Utara</title>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <!-- CSS Map -->
    <link rel="stylesheet" href="<?= base_url('css/map.css') ?>">
</head>
<body>

<h2 class="title">Peta Interaktif Sumatera Utara</h2>

<div class="map-wrapper">
    <button onclick="resetMap()" class="btn-reset">🔄 Tampilkan Semua Wilayah</button>
    <div id="map"></div>
    <div id="labelHover" class="label-hover"></div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- JS Map -->
<script src="<?= base_url('js/map.js') ?>"></script>


<script>
document.addEventListener("DOMContentLoaded", async () => {

    /* ================================
       INISIALISASI MAP
    ================================ */
    const map = L.map("map", {
        zoomControl: true,
        scrollWheelZoom: true,
        dragging: true,
        doubleClickZoom: true,
        touchZoom: true
    }).setView([2.2, 99.1], 7);

    const geojsonData = await (await fetch(`<?= base_url("data/sumut.geojson") ?>`)).json();

    let geojsonLayer;
    let activeLayer = null;
    let defaultBounds = null;

    /* ================================
       HELPER WARNA
    ================================ */
    function randomColor() {
        const colors = ["#dbeafe", "#bfdbfe", "#93c5fd", "#60a5fa", "#3b82f6"];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    function style() {
        return {
            color: "#0b2b3a",
            weight: 1.2,
            fillColor: randomColor(),
            fillOpacity: 0.85
        };
    }

    /* ================================
       HOVER LABEL
    ================================ */
    const labelHover = document.getElementById("labelHover");

    function onEachFeature(feature, layer) {
        const nama = feature.properties.NAME_2 || "Wilayah";

        layer.on("mousemove", e => {
            labelHover.style.display = "block";
            labelHover.style.left = (e.originalEvent.pageX + 12) + "px";
            labelHover.style.top  = (e.originalEvent.pageY - 16) + "px";
            labelHover.innerHTML = nama;
        });

        layer.on("mouseout", () => {
            labelHover.style.display = "none";
        });

        /* ================================
           KLIK → FOKUS 1 WILAYAH SAJA
        ================================ */
        layer.on("click", () => {
            fokusWilayah(layer);
            layer.bindPopup(`<b>${nama}</b>`).openPopup();
        });
    }

    /* ================================
       RENDER GEOJSON
    ================================ */
    geojsonLayer = L.geoJSON(geojsonData, {
        style,
        onEachFeature
    }).addTo(map);

    defaultBounds = geojsonLayer.getBounds();
    map.fitBounds(defaultBounds);

    /* ================================
       FUNGSI FOKUS 1 WILAYAH
    ================================ */
    window.fokusWilayah = function(layer) {
        activeLayer = layer;

        geojsonLayer.eachLayer(l => {
            if (l !== layer) {
                l.setStyle({
                    fillOpacity: 0,
                    opacity: 0
                });
            }
        });

        layer.setStyle({
            fillOpacity: 1,
            opacity: 1,
            weight: 3
        });

        map.fitBounds(layer.getBounds(), {
            padding: [20,20],
            maxZoom: 11
        });
    };

    /* ================================
       RESET → TAMPILKAN SEMUA LAGI
    ================================ */
    window.resetMap = function() {
        geojsonLayer.eachLayer(layer => {
            geojsonLayer.resetStyle(layer);
        });

        activeLayer = null;
        map.fitBounds(defaultBounds);
    };

});
</script>
</body>
</html>
