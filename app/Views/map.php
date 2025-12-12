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

    /* ============================================================
       MAP INIT
    ============================================================ */
    const map = L.map("map", {
        zoomControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        touchZoom: false,
        boxZoom: false,
        keyboard: false
    }).setView([2.2, 99.1], 7);

    /* ============================================================
       LOAD GEOJSON
    ============================================================ */
    const geojsonData = await (await fetch("<?= base_url('data/sumut.geojson') ?>")).json();
    const titikData   = await (await fetch("<?= base_url('data/titik_koordinat.geojson') ?>")).json();

    let markerLayer = L.layerGroup();

    /* ============================================================
       HELPERS
    ============================================================ */

    // Ambil nama wilayah dengan fallback yang aman
    function getNamaWilayah(props) {
        return props.NAME_2 ||
               props.NAME_1 ||
               props.kabupaten_ ||
               props.kabupaten_kota ||
               props.kab_kota ||
               "Wilayah";
    }

    // Ambil nama PT (banyak variasi dari API)
    function getNamaPT(props) {
        return props.nama_pt || props.NAMA_PT || "Kampus";
    }

    // Normalisasi nama wilayah
    function normalizeName(s) {
        if (!s && s !== 0) return "";
        return String(s)
            .replace(/_/g, " ")
            .replace(/\bKABUPATEN\b/gi, "KAB")
            .replace(/\bKAB\.\b/gi, "KAB")
            .replace(/\bKAB\b/gi, "KAB")
            .replace(/\bKOTA\b/gi, "KOTA")
            .replace(/[.,()\/\\-]/g, " ")
            .replace(/\s+/g, " ")
            .trim()
            .toUpperCase();
    }

    // Cocokkan polygon vs titik berdasarkan nama
    function isSameWilayah(polygonName, titikName) {
        if (!polygonName || !titikName) return false;

        if (polygonName === titikName) return true;
        if (normalizeName(polygonName) === normalizeName(titikName)) return true;

        const p = normalizeName(polygonName).replace(/^KAB\s+/i, "").replace(/^KOTA\s+/i, "");
        const t = normalizeName(titikName).replace(/^KAB\s+/i, "").replace(/^KOTA\s+/i, "");

        return (p === t || p.includes(t) || t.includes(p));
    }

    // Warna random polygon
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

    /* ============================================================
       DOM ELEMENTS
    ============================================================ */
    const labelHover     = document.getElementById("labelHover");
    const panelInfo      = document.getElementById("panelInfo");
    const panelNama      = document.getElementById("panelNama");
    const panelIsi       = document.getElementById("panelIsi");
    const panelList      = document.getElementById("panelList");
    const panelDetail    = document.getElementById("panelDetail");
    const daftarWilayah  = document.getElementById("daftarWilayah");
    const mapElement     = document.getElementById("map");
    const infoOverlay    = document.getElementById("infoOverlay");
    const judulWilayah   = document.getElementById("judulWilayah");
    const ptsCount       = document.getElementById("ptsCount");
    const bentukList     = document.getElementById("bentukList");

    let geojson = null;
    let defaultBounds = null;

    /* ============================================================
       SHOW MARKERS
    ============================================================ */
    function showMarkersForFeatures(features) {

        markerLayer.clearLayers();

        if (!features || features.length === 0) return;

        features.forEach(f => {
            let lat, lng;

            if (f.geometry?.coordinates) {
                lng = Number(f.geometry.coordinates[0]);
                lat = Number(f.geometry.coordinates[1]);
            } else {
                lat = Number(f.properties.Latitude  ?? f.properties.lat);
                lng = Number(f.properties.Longitude ?? f.properties.lng);
            }

            if (!isFinite(lat) || !isFinite(lng)) return;

            const mk = L.circleMarker([lat, lng], {
                radius: 6,
                color: "#0d47a1",
                weight: 1.5,
                fillColor: "#42a5f5",
                fillOpacity: 0.9
            });

            mk.bindTooltip(getNamaPT(f.properties), {
                direction: "top",
                offset: [0, -6]
            });

            mk.on("mouseover", () => mk.setStyle({ radius: 8, fillOpacity: 1 }));
            mk.on("mouseout",  () => mk.setStyle({ radius: 6, fillOpacity: 0.9 }));

            mk.on("click", () => {
                panelInfo.classList.add("panel-muncul");
                panelList.style.display = "none";
                panelDetail.style.display = "block";
                mapElement.classList.add("map-kiri");

                panelNama.innerText = getNamaPT(f.properties);

                panelIsi.innerHTML = `
                    <table class="table-kampus">
                        <tr><td><b>Kode PT</b></td><td>${f.properties.kode_pt ?? "-"}</td></tr>
                        <tr><td><b>Nama PT</b></td><td>${getNamaPT(f.properties)}</td></tr>
                        <tr><td><b>Bentuk</b></td><td>${f.properties.bentuk_pt ?? "-"}</td></tr>
                        <tr><td><b>Wilayah</b></td><td>${f.properties.NAME_2_ ?? f.properties.kabupaten_ ?? "-"}</td></tr>
                        <tr><td><b>Alamat</b></td><td>${f.properties.alamat ?? "-"}</td></tr>
                        <tr><td><b>Latitude</b></td><td>${lat}</td></tr>
                        <tr><td><b>Longitude</b></td><td>${lng}</td></tr>
                    </table>
                `;

                map.flyTo([lat, lng], 13, { duration: 0.6 });
            });

            markerLayer.addLayer(mk);
        });

        markerLayer.addTo(map);
    }

    /* ============================================================
       LOAD TOTAL SUMUT
    ============================================================ */
    function loadTotalSumut() {
        fetch("<?= base_url('map/total-sumut') ?>")
            .then(res => res.json())
            .then(data => {
                judulWilayah.innerText = "Provinsi Sumatera Utara";
                ptsCount.innerText = data.total_pts ?? 0;

                bentukList.innerHTML = `
                    <li>Universitas: ${data.universitas ?? 0}</li>
                    <li>Institut: ${data.institut ?? 0}</li>
                    <li>Sekolah Tinggi: ${data.sekolah_tinggi ?? 0}</li>
                    <li>Politeknik: ${data.politeknik ?? 0}</li>
                    <li>Akademi: ${data.akademi ?? 0}</li>
                `;
                infoOverlay.style.display = "block";
            })
            .catch(console.error);
    }

    /* ============================================================
       LOAD DATA WILAYAH + MARKER
    ============================================================ */
    function loadWilayahDB(namaWilayah) {
        fetch("<?= base_url('map/wilayah') ?>/" + encodeURIComponent(namaWilayah))
            .then(res => res.json())
            .then(data => {
                infoOverlay.style.display = "none";

                panelNama.innerText = data.wilayah ?? namaWilayah;
                panelIsi.innerHTML = `
                    <p><b>Total PTS:</b> ${data.total_pts ?? "-"}</p>
                    <ul>
                        <li>Universitas: ${data.universitas ?? 0}</li>
                        <li>Institut: ${data.institut ?? 0}</li>
                        <li>Sekolah Tinggi: ${data.sekolah_tinggi ?? 0}</li>
                        <li>Politeknik: ${data.politeknik ?? 0}</li>
                        <li>Akademi: ${data.akademi ?? 0}</li>
                    </ul>
                `;
            })
            .catch(() => {
                panelNama.innerText = namaWilayah;
                panelIsi.innerHTML = `<p>Data wilayah tidak tersedia.</p>`;
            });

        const matched = (titikData.features || []).filter(f => {
            const kandidat = [
                f.properties.NAME_2,
                f.properties.NAME_2_,
                f.properties.kabupaten_,
                f.properties.kabupaten_kota,
                f.properties.kab_kota
            ].filter(Boolean);

            return kandidat.some(t => isSameWilayah(namaWilayah, t));
        });

        showMarkersForFeatures(matched);
    }

    /* ============================================================
       CLICK POLYGON
    ============================================================ */
    function aksiKlikWilayah(layer, nama) {

        geojson.eachLayer(l => {
            if (l !== layer) l.setStyle({ fillOpacity: 0, opacity: 0 });
        });

        try {
            map.flyToBounds(layer.getBounds(), { padding: [30,30], maxZoom: 11, duration: 0.8 });
        } catch {}

        mapElement.classList.add("map-kiri");
        panelInfo.classList.add("panel-muncul");

        infoOverlay.style.display = "none";
        panelList.style.display = "none";
        panelDetail.style.display = "block";

        loadWilayahDB(nama);
    }

    function onEachFeature(feature, layer) {
        const nama = getNamaWilayah(feature.properties);

        layer.on("mousemove", e => {
            labelHover.style.display = "block";
            labelHover.style.left = (e.originalEvent.pageX + 12) + "px";
            labelHover.style.top  = (e.originalEvent.pageY - 18) + "px";
            labelHover.innerHTML = nama;
        });

        layer.on("mouseout", () => labelHover.style.display = "none");

        layer.on("click", () => aksiKlikWilayah(layer, nama));
    }

    /* ============================================================
       RENDER GEOJSON
    ============================================================ */
    geojson = L.geoJSON(geojsonData, { style: baseStyle, onEachFeature }).addTo(map);

    defaultBounds = geojson.getBounds();
    map.fitBounds(defaultBounds);

    /* ============================================================
       POPULATE LIST WILAYAH
    ============================================================ */
    geojson.eachLayer(layer => {
        const nama = getNamaWilayah(layer.feature.properties);

        const li = document.createElement("li");
        li.textContent = nama;
        li.style.cursor = "pointer";
        li.style.padding = "8px 0";
        li.onclick = () => aksiKlikWilayah(layer, nama);

        daftarWilayah.appendChild(li);
    });

    /* ============================================================
       RESET BUTTON
    ============================================================ */
    window.resetMap = function () {

        geojson.eachLayer(l => l.setStyle({ fillOpacity: 0.9, opacity: 1 }));

        markerLayer.clearLayers();

        map.flyToBounds(defaultBounds, { padding: [30,30], duration: 0.8 });

        mapElement.classList.remove("map-kiri");
        panelInfo.classList.remove("panel-muncul");

        panelDetail.style.display = "none";
        panelList.style.display = "block";

        infoOverlay.style.display = "block";

        loadTotalSumut();
    };

    /* ============================================================
       LOAD AWAL
    ============================================================ */
    loadTotalSumut();

});
</script>

</body>
</html>
