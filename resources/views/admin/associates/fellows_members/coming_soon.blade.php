@extends('layout.app')  

@section('content')
  
<div class="wrapper">

    <div class="container-new">
        <h1>Secretariat Dashboard</h1>
        <p>We are launching soon! Stay tuned for updates.</p>
        <div class="countdown" id="countdown">00:00:00:00</div>
        <div class="section">
            <h2>Fellows</h2>
            <p>Details about the fellows program.</p>
            <a href="#" class="btn btn-primary">Learn More</a>
        </div>
        <div class="section">
            <h2>Members</h2>
            <p>Details about the members program.</p>
            <a href="#" class="btn btn-secondary">Learn More</a>
        </div>
        <div class="section">
            <h2>Examination</h2>
            <p>Information about upcoming examinations.</p>
            <a href="#" class="btn btn-info">Learn More</a>
        </div>
        <div class="section">
            <p>Contact us at <a href="mailto:admission@cosecsa.org">admission@cosecsa.org</a> or follow us on <a href="#">social media</a>.</p>
        </div>
    </div>

</div>

@endsection

<script>
    // Countdown timer script
    (function() {
        const launchDate = new Date('2024-12-31T00:00:00').getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = launchDate - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('countdown').innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;

            if (distance < 0) {
                clearInterval(countdownInterval);
                document.getElementById('countdown').innerHTML = 'We are live!';
            }
        }

        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
    })();
</script>


<style>
    body, html {
        height: 100%;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f7f7f7;
        color: #333;
    }
    .container-new {
        text-align: center;
        padding: 30px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .countdown {
        font-size: 2em;
        margin: 20px 0;
    }
    .section {
        margin-top: 40px;
    }
</style>

