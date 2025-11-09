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
                                            <span>Online Cars: </span><span id="cars">0</span>
                                        </div>
                                    </div>
                                    <div class="faq row">
                           
                           
                           <div class="col s12 l12">
                           <div class="row">
                           <div class="col s12 l12">
                           <div id="map">
                           </div></div>
                      
                        
                    
                        
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
<!-- jQuery CDN -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

<!-- Firebase -->
<script src="https://www.gstatic.com/firebasejs/4.12.1/firebase.js"></script>
<script>
    // Replace your Configuration here..
var config = {
    apiKey: "AIzaSyBk2-uqXkC_0TxCQcIzgvVyrCr9icSUZMs",
    authDomain: "rushwheelz-c0dc9.firebaseapp.com",
    databaseURL: "https://rushwheelz-c0dc9.firebaseio.com",
    projectId: "rushwheelz-c0dc9",
    storageBucket: "rushwheelz-c0dc9.appspot.com",
    messagingSenderId: "699570198340",
    appId: "1:699570198340:web:3ec3c63984f5cbf9d92911"
};
    firebase.initializeApp(config);
</script>

<script>

    // counter for online cars...
    var cars_count = 0;

    // markers array to store all the markers, so that we could remove marker when any car goes offline and its data will be remove from realtime database...
    var markers = [];
    var map,marker;
    var speed = 50; // km/h
  
    function initMap() { // Google Map Initialization... 
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 16,
            center: new google.maps.LatLng(parseFloat(42.4062955179642), parseFloat(-71.23772614503424)),
            mapTypeId: 'roadmap'
        });
    
    }
  

          
    // This Function will create a car icon with angle and add/display that marker on the map
    function AddCar(data) {

        var icon = { // car icon
            path: 'M270 400 c0 -5 10 -10 23 -11 17 -1 15 -3 -10 -10 -18 -5 -35 -14 -38 -19 -3 -6 -49 -10 -101 -10 -56 0 -94 -4 -94 -10 0 -5 8 -10 18 -10 10 0 35 -9 57 -20 22 -11 45 -20 52 -20 16 0 16 -5 1 -29 -12 -19 -12 -19 -36 0 -34 26 -78 24 -107 -6 -51 -50 -17 -135 54 -135 41 0 63 17 80 64 10 25 15 27 39 20 15 -4 38 -8 52 -8 21 -1 25 4 28 32 2 17 -1 32 -7 32 -5 0 -16 3 -25 6 -11 4 -16 1 -16 -10 0 -18 -8 -20 -29 -7 -20 13 5 55 27 45 37 -15 59 -15 71 0 18 22 26 20 39 -8 9 -19 8 -27 -4 -36 -8 -7 -14 -30 -14 -50 0 -72 85 -106 135 -55 52 51 17 135 -56 135 -21 0 -39 2 -39 4 0 2 -3 11 -6 20 -5 13 1 16 25 16 17 0 31 5 31 10 0 6 -17 10 -38 10 -34 0 -39 3 -51 35 -9 27 -18 35 -37 35 -13 0 -24 -4 -24 -10z m-145 -154 c19 -13 18 -15 -15 -23 -20 -5 -35 -16 -35 -24 0 -14 13 -15 53 -3 13 4 22 2 22 -5 0 -18 -39 -51 -60 -51 -26 0 -60 34 -60 60 0 25 34 60 58 60 10 0 27 -6 37 -14z m325 -6 c11 -11 20 -29 20 -40 0 -26 -34 -60 -60 -60 -41 0 -73 54 -52 87 6 9 13 4 26 -17 18 -32 30 -37 42 -18 4 7 -2 18 -14 26 -12 8 -22 21 -22 28 0 21 37 17 60 -6z',
            scale: 0.1,
            fillColor: "#ff7121", //<-- Car Color, you can change it 
            fillOpacity: 1,
            strokeWeight: 1,
            anchor: new google.maps.Point(0, 5),
            rotation: data.val().angle //<-- Car angle
        };

        var uluru = { lat: parseFloat(data.val().lat), lng: parseFloat(data.val().lng) };
// alert(uluru);
        var marker = new google.maps.Marker({
            position: uluru,
            icon: icon,
            map: map,
            title: 'Easy Go Driver'
        });
    //     $.ajax({
    //     type: "get",
    //     dataType : "JSON",
    //     url: "<?php echo Url::base()?>/admin/users/get-driver?id="+data.val().id, 
      
    //     success: function(response){
    //         var json = JSON.parse(JSON.stringify(response));
          
    //          var contentString = '<div id="content">'+
    //         '<div id="siteNotice">'+
    //         '</div>'+
            
           
    //         '<h5 id="firstHeading" class="firstHeading">'+json.user.full_name+'</h5>'+
    //         '<h6 id="firstHeading" class="firstHeading">'+json.user.contact_no+'</h6>'+
           
    //        // '<span class="title">Order id : # '+json.model[0].order_id+'</span><br>8367536212<p></p>'+
          
     
           
          
    //          '</ul>'+
           
    //         '</div>'+
    //         '</div>';
    //         var infowindow = new google.maps.InfoWindow({
    //       content: contentString,
    //       maxWidth: 400
    //     });
    //     marker.addListener('click', function() {
    //       infowindow.open(map, marker);
    //     });
    //     }
    //  });
      
      
        
        markers[data.key] = marker; // add marker in the markers array...
        document.getElementById("cars").innerHTML = cars_count;
    }

    // get firebase database reference...
    var cars_Ref = firebase.database().ref('/');
// alert(cars_Ref);
console.log(cars_Ref)
    // this event will be triggered when a new object will be added in the database...
    cars_Ref.on('child_added', function (data) {
    //  alert(cars_count);
        cars_count++;
        AddCar(data);
    });
   

    // this event will be triggered on location change of any car...
    cars_Ref.on('child_changed', function (data) {
        markers[data.key].setMap(null);
        //   alert('ghdfghfdg');
        AddCar(data);
    });

    // If any car goes offline then this event will get triggered and we'll remove the marker of that car...  
    cars_Ref.on('child_removed', function (data) {
        markers[data.key].setMap(null);
        cars_count--;
        document.getElementById("cars").innerHTML = cars_count;
    });

</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3NIZ4q7i7EOraQPEyN4pUL6jchR3Rv-8&callback=initMap">
</script>