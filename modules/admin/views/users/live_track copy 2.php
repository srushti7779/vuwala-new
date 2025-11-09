<?php

use yii\helpers\Url;
?>
<!-- use dosamigos\google\maps\overlays\InfoWindow; -->

<style>
    #map {
        width: 100%;
        height: 500px;
        background-color: grey;
    }
</style>

<div class="section" id="faq">
    <div class="row">
        <div class="col s12">
            <div id="faq-search" class="card z-depth-0 faq-search-image center-align p-35">
                <div class="card-content">
                    <div id="over_map">
                        <div>
                            <span>Online Bikes: </span><span id="cars">0</span>
                        </div>
                    </div>
                    <div class="faq row">


                        <div class="col s12 l12">
                            <div class="row">
                                <div class="col s12 l12">
                                    <div id="map">
                                    </div>
                                </div>




                            </div>
                            <div id="list" class="col s12">


                                <div id="offline" class="col s12">
                                    <div class="card-content">
                                        <ul class="collection mb-0">




                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>




        </div>
    </div>
</div>


<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3NIZ4q7i7EOraQPEyN4pUL6jchR3Rv-8&callback=initMap"></script>
<script>
    // counter for online cars...
    var cars_count = 0;
    var markers = [];
    var map, marker;
    var speed = 50; // km/h

    function initMap() { // Google Map Initialization... 
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 16,
            center: new google.maps.LatLng(parseFloat(42.4062955179642), parseFloat(-71.23772614503424)),
            mapTypeId: 'roadmap'
        });

    }
</script>

<script src="https://www.gstatic.com/firebasejs/9.16.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.16.0/firebase-firestore-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.16.0/firebase-auth-compat.js"></script>
<script>
   const firebaseApp = firebase.initializeApp({ 
    
apiKey: "AIzaSyAJTNvsxVxnL8GljPgKUOX6I_Y1JKawS3Q",
  authDomain: "easygo-transport.firebaseapp.com",
  databaseURL: "https://easygo-transport-default-rtdb.asia-southeast1.firebasedatabase.app",
  projectId: "easygo-transport",
  storageBucket: "easygo-transport.appspot.com",
  messagingSenderId: "1075775162016",
  appId: "1:1075775162016:web:4215f1e0416bddd9832bc5",
  measurementId: "G-LSQ78G5R0L"


   });
   const db = firebaseApp.firestore();






</script>


