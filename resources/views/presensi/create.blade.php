@extends('layouts.presensi')

@section('header')
<!-- App Header -->
<div class="appHeader bg-primary text-light">
    <div class="left">
        <a href="javascript:;" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">E-Presensi SADEWA</div>
    <div class="right"></div>
</div>
<!-- \* App Header -->
<style>
    .webcam-capture,
    .webcam-capture video {
        display: inline-block;
        position: absolute;
        top: 0;
        left: 0;
        width: 100% !important;
        height: auto !important;
        border-radius: 15px;
        object-fit: cover;
    }

    #map { 
        height: 300px; 
    }

</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin="">
</script>

@endsection

@section('content')
<div class="row" style="margin-top: 70px">
    <div class="col">
        <input type="hidden" id="lokasi">
        <div id="webcam-capture" class="webcam-capture"></div>
    </div>
</div>

<div class="row" style="margin-top: 60%">
    <div class="col">
    @if ($cek > 0)
    <button id="takeabsen" class="btn btn-danger btn-block">
        <ion-icon name="camera-outline"></ion-icon>Absen Pulang
    </button>
    @else
    <button id="takeabsen" class="btn btn-primary btn-block">
        <ion-icon name="camera-outline"></ion-icon>Absen Masuk
    </button>
    @endif

    </div>   
</div>
<div class="row mt-2">
    <div class="col">
    <div id="map"></div>
    </div>
</div>
<audio id="masuk-audio" src="{{ asset('assets/sound/notifin.mp3') }}" preload="auto"></audio>
<audio id="pulang-audio" src="{{ asset('assets/sound/notifout.mp3') }}" preload="auto"></audio>

@endsection

@push('myscript')
<script>
    Webcam.set({
        width: 480,
        height: 640,
        image_format: 'jpeg',
        jpeg_quality: 80
    });

    Webcam.attach('#webcam-capture');
            
    var lokasi = document.getElementById('lokasi');
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(successCallback, errorCallback)
    }

    function successCallback(position){
        lokasi.value = position.coords.latitude + "," + position.coords.longitude;
        var map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 18);
        var lok_kerja = "{{ $lok_kerja->lokasi }}";        
        var lok = lok_kerja.split(",");
        var lat_kantor = lok[0];
        var long_kantor = lok[1];
        var radius = "{{ $lok_kerja->radius }}";
        L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            }).addTo(map);
        var marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
        var circle = L.circle([lat_kantor,long_kantor], {
        color: 'red',
        fillColor: '#f03',
        fillOpacity: 0.5,
        radius: radius
        }).addTo(map);
    }
    function errorCallback(){

    }
    
    
    $("#takeabsen").click(function(e) {
    var image;
    Webcam.snap(function(uri) {
        image = uri;
    });

    var lokasi = $("#lokasi").val();
    var cek = "{{ $cek }}";
    $.ajax({
        type: 'POST',
        url: '/presensi/store',
        data: {
            _token: "{{ csrf_token() }}",
            image: image,
            lokasi: lokasi,
            cek: cek
        },
        cache: false,
        success: function(respond) {
            if (respond.error) {
                var audioError = new Audio('{{ asset("assets/sound/radius.mp3") }}');
                audioError.play();
                Swal.fire({
                    title: 'Error!',
                    text: respond.error,
                    icon: 'error',
                    confirmButtonText: 'Oke'
                });
            } else {
                var message = '';
                var audioElement = null;

                if (respond.jenis_absen == 'masuk') {
                    message = 'Terimakasih, Selamat Bekerja';
                    audioElement = document.getElementById('masuk-audio');
                } else if (respond.jenis_absen == 'pulang') {
                    message = 'Terimakasih Untuk Hari Ini';
                    audioElement = document.getElementById('pulang-audio');
                }

                if (audioElement) {
                    audioElement.play();
                }

                Swal.fire({
                    title: 'Berhasil!',
                    text: message,
                    icon: 'success'
                });

                setTimeout(function() {
                    window.location.href = '/dashboard';
                }, 3000);
                }
            }
        });
    });

</script>
@endpush
 