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

        var flightPath = new google.maps.Polyline({
            path: window.times.ontime,
            geodesic: true,
            strokeColor: '#1e7005', // Green is for ontime
            strokeOpacity: 1.0,
            strokeWeight: 2
        });

        var earlyMap = new google.maps.Polyline({
            path: window.times.early,
            geodesic: true,
            strokeColor: '#68c1b7', // Cyan is early
            strokeOpacity: 1.0,
            strokeWeight: 2
        });

        var lateMap = new google.maps.Polyline({
            path: window.times.late,
            geodesic: true,
            strokeColor: '#af180e', // Red is late
            strokeOpacity: 1.0,
            strokeWeight: 2
        });

        flightPath.setMap(map);
        earlyMap.setMap(map);
        lateMap.setMap(map);
    }
</script>

<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAGIgo1ZHPPQRNGW_p0RAHQ8MPRsyXAXxM&callback=initMap">
</script>
</body>
</html>
