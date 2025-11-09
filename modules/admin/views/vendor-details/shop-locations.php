<?php
$this->title = 'Vendor Shop Locations';
$this->registerJsFile('https://maps.googleapis.com/maps/api/js?key=AIzaSyD68kxLx285OInWNU7TuSg5QHda1Ih_E_U', ['position' => \yii\web\View::POS_HEAD]);
?>

<h1 style ="color:navy;">
    🛍️ Vendor Shop Locations
</h1>

<div id="map" style="
    height: 600px; 
    width: 100%; 
    border-radius:12px; 
    background-color:#dfe6e9; /* fallback background (no black) */
    /* box-shadow:0px 6px 14px rgba(0,0,0,0.25); */
"></div>

<style>
    .infowindow-card {
        font-family: 'Segoe UI', sans-serif;
        background: #ffffff;
        border-radius: 12px;
        padding: 12px;
        max-width: 280px;
        border: 2px solid #1976d2;
        box-shadow: 0px 6px 14px rgba(0,0,0,0.25);
    }
    .infowindow-card h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #1e272e;
    }
    .infowindow-card p {
        margin: 4px 0;
        font-size: 13px;
        color: #444;
    }

    .infowindow-card img {
        margin-top: 8px;
        width: 100%;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid #ccc;
    }
    .rating {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        background: #e67e22;
        color: #fff;
    }
</style>

<script>
function initMap() {
    var mapOptions = {
        zoom: 5,
        center: { lat: 17.385044, lng: 78.486671 }, // Hyderabad
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        backgroundColor: "#dfe6e9", // no black screen
        styles: [
            { "elementType": "geometry", "stylers": [{ "color": "#f5f5f5" }] },
            { "featureType": "water", "elementType": "geometry.fill", "stylers": [{ "color": "#1976d2" }] },
            { "featureType": "road", "elementType": "geometry", "stylers": [{ "color": "#ffffff" }] },
            { "featureType": "road.highway", "elementType": "geometry", "stylers": [{ "color": "#e67e22" }] },
            { "featureType": "road.arterial", "elementType": "geometry", "stylers": [{ "color": "#2ecc71" }] },
            { "featureType": "landscape", "elementType": "geometry.fill", "stylers": [{ "color": "#f1c40f" }] },
            { "elementType": "labels.text.fill", "stylers": [{ "color": "#2c3e50" }] },
            { "elementType": "labels.text.stroke", "stylers": [{ "color": "#ffffff" }] },
            { "featureType": "poi", "stylers": [{ "visibility": "off" }] }
        ]
    };

    var map = new google.maps.Map(document.getElementById('map'), mapOptions);

    var markers = <?= json_encode(array_map(function($vendor) {
        return [
            'lat' => (float)$vendor->latitude,
            'lng' => (float)$vendor->longitude,
            'business_name' => $vendor->business_name,
            'address' => $vendor->address,
            'rating' => $vendor->avg_rating,
            'logo' => $vendor->logo,
        ];
    }, $model)) ?>;

    markers.forEach(function(vendor, index) {
        // Rotate marker colors for variety
        var colors = ["red", "blue", "green", "orange", "purple"];
        var markerColor = colors[index % colors.length];

        var marker = new google.maps.Marker({
            position: { lat: vendor.lat, lng: vendor.lng },
            map: map,
            title: vendor.business_name,
            icon: "http://maps.google.com/mapfiles/ms/icons/" + markerColor + "-dot.png"
        });

        var content = `
            <div class="infowindow-card">
                <h4>${vendor.business_name}</h4>
                <p>${vendor.address}</p>
                <p><span class="rating">⭐ ${vendor.rating ?? 'N/A'}</span></p>
                ${vendor.logo ? '<img src="' + vendor.logo + '" alt="Logo">' : ''}
            </div>
        `;

        var infowindow = new google.maps.InfoWindow({ content: content });

        marker.addListener('click', function() {
            infowindow.open(map, marker);
        });
    });
}

window.onload = initMap;
</script>
