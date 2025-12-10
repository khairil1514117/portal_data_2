<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Map Sumatera Utara</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <!-- CSS Anda (nanti Anda kirim setelah ini) -->
    <link rel="stylesheet" href="<?= base_url('css/map.css') ?>">
</head>
<body>

<!-- ✅ TOMBOL RESET (KIRI BAWAH) -->
<button onclick="resetMap()" class="btn-reset">Back</button>

<!-- ✅ WRAPPER MAP + PANEL -->
<div class="map-container">

    <!-- ✅ PANEL 1 = MAP -->
    <div id="map">

        <div class="map-logo-wrapper">
            <img src="<?= base_url('img/LLDIKTI.png') ?>" class="map-logo">
            <img src="<?= base_url('img/DIKTISAINTEK.png') ?>" class="map-logo">
        </div>

    </div>

    <!-- ✅ POPUP KETERANGAN TUNGGAL (INI ADALAH VERSI KE-2 YANG DIPAKAI) -->
    <div id="infoOverlay" class="info-overlay">
        <h4 id="judulWilayah">Provinsi Sumatera Utara</h4>
        <p>Total PTS: <b id="ptsCount">0</b></p>
        <ul id="bentukList"></ul>
    </div>

    <!-- ✅ PANEL KANAN -->
    <div id="panelInfo" class="panel-info">

        <div id="panelList">
            <h3>Daftar Kabupaten / Kota</h3>
            <ul id="daftarWilayah"></ul>
        </div>

        <div id="panelDetail" style="display:none;">
            <h3 id="panelNama">Wilayah</h3>
            <div id="panelIsi"></div>
        </div>

    </div>

</div>

<!-- ✅ LABEL HOVER -->
<div id="labelHover" class="label-hover"></div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener("DOMContentLoaded", async () => {

    const map = L.map("map", {
        zoomControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        touchZoom: false,
        boxZoom: false,
        keyboard: false
    }).setView([2.2, 99.1], 7);

    const response = await fetch("<?= base_url('data/sumut.geojson') ?>");
    const geojsonData = await response.json();

    function randomColor() {
        const colors = ["#5adbb0", "#72e980", "#84aa86", "#4dd86d", "#75c162"];
        return colors[Math.floor(Math.random() * colors.length)];
    }

    function baseStyle() {
        return {
            color: "#0b2b3a",
            weight: 1.4,
            fillColor: randomColor(),
            fillOpacity: 0.9
        };
    }

    const labelHover     = document.getElementById("labelHover");
    const panelInfo      = document.getElementById("panelInfo");
    const panelNama      = document.getElementById("panelNama");
    const panelIsi       = document.getElementById("panelIsi");
    const panelList      = document.getElementById("panelList");
    const panelDetail    = document.getElementById("panelDetail");
    const daftarWilayah  = document.getElementById("daftarWilayah");
    const mapElement     = document.getElementById("map");

    const judulWilayah = document.getElementById("judulWilayah");
    const ptsCount     = document.getElementById("ptsCount");
    const bentukList   = document.getElementById("bentukList");
    const infoOverlay  = document.getElementById("infoOverlay");

    let defaultBounds = null;
    let geojson = null;

    // ===============================
    // ✅ LOAD TOTAL DATA SUMUT (DEFAULT)
    // ===============================
    function loadTotalSumut() {
        fetch("<?= base_url('map/total-sumut') ?>")
            .then(res => res.json())
            .then(data => {

                judulWilayah.innerText = "Provinsi Sumatera Utara";
                ptsCount.innerText = data.total_pts;

                bentukList.innerHTML = `
                    <li>Universitas: ${data.universitas}</li>
                    <li>Institut: ${data.institut}</li>
                    <li>Sekolah Tinggi: ${data.sekolah_tinggi}</li>
                    <li>Politeknik: ${data.politeknik}</li>
                    <li>Akademi: ${data.akademi}</li>
                `;

                infoOverlay.style.display = "block";
            });
    }

    // ===============================
    // ✅ LOAD DATA PER WILAYAH
    // ===============================
    function loadWilayahDB(namaWilayah) {
        fetch("<?= base_url('map/wilayah') ?>/" + encodeURIComponent(namaWilayah))
            .then(res => res.json())
            .then(data => {

                // ✅ POPUP KETERANGAN DISSEMBUNYIKAN SAAT KLIK WILAYAH
                infoOverlay.style.display = "none";

                panelNama.innerText = data.wilayah;
                panelIsi.innerHTML = `
                    <p><b>Total PTS:</b> ${data.total_pts}</p>
                    <ul>
                        <li>Universitas: ${data.universitas}</li>
                        <li>Institut: ${data.institut}</li>
                        <li>Sekolah Tinggi: ${data.sekolah_tinggi}</li>
                        <li>Politeknik: ${data.politeknik}</li>
                        <li>Akademi: ${data.akademi}</li>
                    </ul>
                `;
            });
    }

    function aksiKlikWilayah(layer, nama) {

        geojson.eachLayer(l => {
            if (l !== layer) {
                l.setStyle({ fillOpacity: 0, opacity: 0 });
            }
        });

        map.flyToBounds(layer.getBounds(), {
            padding: [30, 30],
            maxZoom: 11,
            duration: 0.8
        });

        mapElement.classList.add("map-kiri");
        panelInfo.classList.add("panel-muncul");

        panelList.style.display = "none";
        panelDetail.style.display = "block";

        loadWilayahDB(nama);
    }

    function onEachFeature(feature, layer) {

        const nama = feature.properties.NAME_2 || "Wilayah";

        layer.on("mousemove", e => {
            labelHover.style.display = "block";
            labelHover.style.left = (e.originalEvent.pageX + 12) + "px";
            labelHover.style.top  = (e.originalEvent.pageY - 18) + "px";
            labelHover.innerHTML = nama;
        });

        layer.on("mouseout", () => {
            labelHover.style.display = "none";
        });

        layer.on("click", () => {
            aksiKlikWilayah(layer, nama);
        });
    }

    geojson = L.geoJSON(geojsonData, {
        style: baseStyle,
        onEachFeature
    }).addTo(map);

    defaultBounds = geojson.getBounds();
    map.fitBounds(defaultBounds);

    geojson.eachLayer(layer => {
        const nama = layer.feature.properties.NAME_2;
        const li = document.createElement("li");

        li.textContent = nama;
        li.style.cursor = "pointer";
        li.style.padding = "8px 0";

        li.onclick = () => {
            aksiKlikWilayah(layer, nama);
        };

        daftarWilayah.appendChild(li);
    });

    // ===============================
    // ✅ FUNGSI RESET (BACK)
    // ===============================
    window.resetMap = function () {

        geojson.eachLayer(l => {
            l.setStyle({
                fillOpacity: 0.9,
                opacity: 1
            });
        });

        map.flyToBounds(defaultBounds, {
            padding: [30,30],
            duration: 0.8
        });

        mapElement.classList.remove("map-kiri");
        panelInfo.classList.remove("panel-muncul");

        panelDetail.style.display = "none";
        panelList.style.display = "block";

        // ✅ POPUP MUNCUL LAGI
        loadTotalSumut();
    };

    // ✅ AUTO LOAD TOTAL SUMUT SAAT PERTAMA DIBUKA
    loadTotalSumut();

});
</script>

</body>
</html>
