<?php
$municipalId = $_SESSION['user_reg'];
$municipal = $conn->prepare("SELECT * FROM tb_municipality WHERE id = ?");
$municipal->execute([$municipalId]);
$municipalData = $municipal->fetch();

$resorts = $conn->prepare("
    SELECT r.*, l.location_id, l.lat, l.lon, l.address as map_address 
    FROM tb_resort r 
    LEFT JOIN tb_location l ON r.resortid = l.resortid 
    WHERE r.mun = ?
");
$resorts->execute([$municipalData['mun']]);

$totalResorts = $conn->prepare("SELECT COUNT(*) as count FROM tb_resort WHERE mun = ?");
$totalResorts->execute([$municipalData['mun']]);
$totalCount = $totalResorts->fetch()['count'];

$withLocation = $conn->prepare("SELECT COUNT(*) as count FROM tb_resort r JOIN tb_location l ON r.resortid = l.resortid WHERE r.mun = ?");
$withLocation->execute([$municipalData['mun']]);
$withLocationCount = $withLocation->fetch()['count'];

$withoutLocation = $conn->prepare("SELECT r.* FROM tb_resort r LEFT JOIN tb_location l ON r.resortid = l.resortid WHERE r.mun = ? AND l.location_id IS NULL");
$withoutLocation->execute([$municipalData['mun']]);
$resortsNoLoc = $withoutLocation->fetchAll();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_location'])) {
    $resortid = $_POST['resortid'];
    $lat = $_POST['lat'];
    $lon = $_POST['lon'];
    $address = $_POST['address'];
    
    $check = $conn->prepare("SELECT location_id FROM tb_location WHERE resortid = ?");
    $check->execute([$resortid]);
    
    if($check->rowCount() > 0) {
        $update = $conn->prepare("UPDATE tb_location SET lat = ?, lon = ?, address = ? WHERE resortid = ?");
        $update->execute([$lat, $lon, $address, $resortid]);
    } else {
        $insert = $conn->prepare("INSERT INTO tb_location (resortid, name, lat, lon, address) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$resortid, $_POST['resortname'], $lat, $lon, $address]);
    }
    
    $updateResort = $conn->prepare("UPDATE tb_resort SET isLocated = 1 WHERE resortid = ?");
    $updateResort->execute([$resortid]);
    
    $success = "Location updated successfully!";
}
?>

<div class="p-6">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Manage Resort Locations</h1>
        <p class="text-gray-500">Update coordinates and addresses for resorts in <?php echo htmlspecialchars($municipalData['mun']); ?></p>
    </div>
    
    <?php if(isset($success)): ?>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-check-circle text-emerald-600 text-xl"></i>
        <span class="text-emerald-700"><?php echo $success; ?></span>
    </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-buildings text-blue-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $totalCount; ?></p>
                    <p class="text-gray-500 text-sm">Total Resorts</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-check-circle text-emerald-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-600"><?php echo $withLocationCount; ?></p>
                    <p class="text-gray-500 text-sm">With Location</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-exclamation-circle text-amber-500 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-600"><?php echo count($resortsNoLoc); ?></p>
                    <p class="text-gray-500 text-sm">Need Location</p>
                </div>
            </div>
        </div>
    </div>

    <?php if(count($resortsNoLoc) > 0): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <i class="bi bi-info-circle text-amber-600 text-xl mt-0.5"></i>
            <div>
                <p class="font-semibold text-amber-800">Resorts without location</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <?php foreach($resortsNoLoc as $r): ?>
                    <button onclick="selectResort(<?php echo $r['resortid']; ?>, '<?php echo htmlspecialchars($r['resortname'], ENT_QUOTES); ?>')" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-amber-300 text-amber-700 rounded-lg text-sm hover:bg-amber-100 transition">
                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($r['resortname']); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 map-section">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div id="map" style="height: 500px; border-radius: 16px;"></div>
            <p class="text-sm text-gray-500 mt-2 text-center">
                Click on the map to set coordinates for the selected resort
            </p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Location Details</h2>
            
            <form method="POST" id="locationForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Resort *</label>
                    <select name="resortid" id="resortSelect" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required>
                        <option value="">Choose a resort...</option>
                        <?php while($resort = $resorts->fetch()): 
                            $hasLoc = $resort['lat'] && $resort['lon'] && $resort['lat'] !== '0' && $resort['lon'] !== '0';
                        ?>
                        <option value="<?php echo $resort['resortid']; ?>" 
                                data-lat="<?php echo $resort['lat']; ?>"
                                data-lon="<?php echo $resort['lon']; ?>"
                                data-address="<?php echo htmlspecialchars($resort['map_address'] ?? $resort['resortaddress']); ?>"
                                data-name="<?php echo htmlspecialchars($resort['resortname']); ?>"
                                data-has-location="<?php echo $hasLoc ? '1' : '0'; ?>">
                            <?php echo htmlspecialchars($resort['resortname']); ?> <?php echo $hasLoc ? '' : '(No location)'; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <div id="locationStatus" class="mt-2 hidden"></div>
                </div>
                
                <input type="hidden" name="resortname" id="resortName">
                <input type="hidden" name="lat" id="latitude">
                <input type="hidden" name="lon" id="longitude">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                    <div class="flex gap-2">
                        <input type="text" id="latDisplay" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="e.g. 10.700000">
                        <button type="button" onclick="setMarkerFromCoords()" class="px-3 py-2.5 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 transition" title="Set marker from coordinates">
                            <i class="bi bi-crosshair"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                    <input type="text" id="lonDisplay" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" placeholder="e.g. 122.550000">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea name="address" id="address" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500" rows="3" required></textarea>
                </div>
                
                <button type="submit" name="update_location" class="w-full py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl font-semibold hover:shadow-lg transition">
                    <i class="bi bi-save me-2"></i> Save Location
                </button>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map;
let marker;
let isUpdatingFromCode = false;

function selectResort(resortId, resortName) {
    const select = document.getElementById('resortSelect');
    for(let i = 0; i < select.options.length; i++) {
        if(select.options[i].value == resortId) {
            select.selectedIndex = i;
            select.dispatchEvent(new Event('change'));
            break;
        }
    }
    document.querySelector('.map-section').scrollIntoView({ behavior: 'smooth' });
}

function syncLatLon(lat, lng) {
    isUpdatingFromCode = true;
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    document.getElementById('latDisplay').value = lat;
    document.getElementById('lonDisplay').value = lng;
    isUpdatingFromCode = false;
}

function placeMarker(lat, lng) {
    if(marker) map.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(map);
}

function setMarkerFromCoords() {
    const lat = parseFloat(document.getElementById('latDisplay').value);
    const lng = parseFloat(document.getElementById('lonDisplay').value);
    if(isNaN(lat) || isNaN(lng)) {
        alert('Please enter valid latitude and longitude values.');
        return;
    }
    syncLatLon(lat.toFixed(6), lng.toFixed(6));
    placeMarker(lat, lng);
    map.setView([lat, lng], 15);
}

map = L.map('map').setView([10.7, 122.55], 12);

L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

map.on('click', function(e) {
    const lat = e.latlng.lat.toFixed(6);
    const lng = e.latlng.lng.toFixed(6);
    placeMarker(lat, lng);
    syncLatLon(lat, lng);
});

document.getElementById('latDisplay').addEventListener('input', function() {
    if(isUpdatingFromCode) return;
    const lat = parseFloat(this.value);
    const lng = parseFloat(document.getElementById('lonDisplay').value);
    if(!isNaN(lat) && !isNaN(lng)) {
        document.getElementById('latitude').value = this.value;
        document.getElementById('longitude').value = document.getElementById('lonDisplay').value;
        placeMarker(lat, lng);
        map.setView([lat, lng], 15);
    }
});

document.getElementById('lonDisplay').addEventListener('input', function() {
    if(isUpdatingFromCode) return;
    const lat = parseFloat(document.getElementById('latDisplay').value);
    const lng = parseFloat(this.value);
    if(!isNaN(lat) && !isNaN(lng)) {
        document.getElementById('latitude').value = document.getElementById('latDisplay').value;
        document.getElementById('longitude').value = this.value;
        placeMarker(lat, lng);
        map.setView([lat, lng], 15);
    }
});

document.getElementById('resortSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const lat = selected.dataset.lat;
    const lon = selected.dataset.lon;
    const address = selected.dataset.address;
    const name = selected.dataset.name;
    const hasLocation = selected.dataset.hasLocation === '1';
    const statusEl = document.getElementById('locationStatus');
    
    document.getElementById('resortName').value = name;
    document.getElementById('address').value = address;
    
    if(!this.value) {
        statusEl.classList.add('hidden');
    } else if(hasLocation) {
        statusEl.classList.remove('hidden');
        statusEl.className = 'mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700';
        statusEl.innerHTML = '<i class="bi bi-check-circle"></i> Location already set — edit fields or click map to update';
    } else {
        statusEl.classList.remove('hidden');
        statusEl.className = 'mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700';
        statusEl.innerHTML = '<i class="bi bi-geo-alt"></i> No location yet — type coordinates or click the map';
    }
    
    if(lat && lon && lat !== '0' && lon !== '0') {
        syncLatLon(lat, lon);
        placeMarker(lat, lon);
        map.setView([lat, lon], 15);
    } else {
        syncLatLon('', '');
        if(marker) {
            map.removeLayer(marker);
            marker = null;
        }
    }
});

<?php
$allResorts = $conn->prepare("SELECT r.resortname, l.lat, l.lon FROM tb_resort r JOIN tb_location l ON r.resortid = l.resortid WHERE r.mun = ?");
$allResorts->execute([$municipalData['mun']]);
while($loc = $allResorts->fetch()):
    if($loc['lat'] && $loc['lon']):
?>
L.marker([<?php echo $loc['lat']; ?>, <?php echo $loc['lon']; ?>])
    .bindPopup("<?php echo addslashes($loc['resortname']); ?>")
    .addTo(map);
<?php
    endif;
endwhile;
?>
</script>
