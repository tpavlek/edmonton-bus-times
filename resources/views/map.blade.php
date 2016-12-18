<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Simple Polylines</title>
    <style>
        /* Always set the map height explicitly to define the size of the div
         * element that contains the map. */
        #map {
            height: 100vh;
        }
        /* Optional: Makes the sample page fill the window. */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
<div id="map"></div>
@include('footer')
<script>
    function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 14,
            center: window.center,
            mapTypeId: 'roadmap'
        });

        var paths = [];

        var flightPlanCoordinates = [
            {lat: 37.772, lng: -122.214},
            {lat: 21.291, lng: -157.821},
            {lat: -18.142, lng: 178.431},
            {lat: -27.467, lng: 153.027}
        ];

        window.times.forEach(function (segment) {
            var line = new google.maps.Polyline({
                path: segment.values,
                geodesic: true,
                strokeColor: segment.color,
                strokeOpacity: 0.7,
                strokeWeight: 4
            });
            paths.push(line);
        });

        paths.forEach(function (polyline) {
            polyline.setMap(map);
        });
    }
</script>

<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAGIgo1ZHPPQRNGW_p0RAHQ8MPRsyXAXxM&callback=initMap">
</script>
</body>
</html>
