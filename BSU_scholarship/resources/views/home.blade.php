@extends('layouts.app')

@section('title', 'Scholarship Portal | Batangas State University')

@push('styles')
    <style>
        @keyframes fadeInDown {
            0% { opacity: 0; transform: translateY(-30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulseButton {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
        .animate-fadeInDown { animation: fadeInDown 1s ease-out forwards; }
        .animate-fadeInUp { animation: fadeInUp 1.2s ease-out forwards; }
        .animate-pulseButton { animation: pulseButton 2s infinite; }
        
        .homepage-bg {
            background-image: url('{{ asset("images/homepage.bg.jpg") }}');
            background-size: cover;
            background-position: center;
        }
    </style>
@endpush

@section('content')
    <!-- Navbar -->
    @include('partials.landing-navbar')

    <!-- Home Section -->
    <section id="home" class="relative min-h-screen flex items-start justify-center text-center overflow-hidden pt-32 homepage-bg">

        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-red-600/90 via-red-300/70 to-white/40"></div>

        <!-- Content -->
        <div class="relative z-10 px-6 md:px-12 max-w-5xl mx-auto flex flex-col items-center">
            <h1 class="font-extrabold text-white drop-shadow-2xl animate-fadeInDown italic text-3xl sm:text-4xl md:text-6xl tracking-wide leading-tight"
                style="text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
                &ldquo;Your Journey to success begins with a 
                <span class="block mt-4 text-red-500 text-5xl sm:text-6xl md:text-8xl filter drop-shadow-lg">Red Spartan Scholarship&rdquo;</span>
            </h1>

            <!-- Apply Button -->
            <div class="animate-fadeInUp delay-300" style="margin-top: 150px;">
                <a href="{{ session()->has('user_id') ? (session('role') == 'student' ? route('student.dashboard') : (session('role') == 'sfao' ? route('sfao.dashboard') : (session('role') == 'central' ? route('central.dashboard') : route('register')))) : route('register') }}" 
                   class="inline-block bg-red-600 text-white px-8 py-3 sm:px-10 sm:py-4 text-base sm:text-lg rounded-full shadow-xl 
                          hover:bg-red-700 hover:scale-110 transition transform duration-300 focus:outline-none focus:ring-4 focus:ring-red-500/50">
                    Apply Now
                </a>
            </div>
        </div>

    </section>




    <!-- About Section -->
    <section id="about" class="pt-24 min-h-screen bg-white">
        <div class="p-6 max-w-6xl mx-auto">
            <h2 class="text-4xl font-bold text-red-700 mb-4">About Us</h2>
            <p class="mt-2 text-gray-600 text-lg leading-relaxed mb-8">
                We are a premier university committed to providing quality education and scholarship opportunities to deserving students. 
                Our mission is to empower scholars for a brighter future through innovation, research, and community service.
            </p>
            <!-- Additional SFAO paragraph -->
            <p class="mt-2 text-gray-700 text-lg leading-relaxed mb-8">
                The Scholarship and Financial Assistance Office (SFAO) of Batangas State University is committed to promoting equitable access to education by providing scholarship and financial assistance programs to deserving students. 
                We uphold integrity, transparency, and excellence in delivering our services, aligned with the university’s vision of becoming a globally recognized institution of higher learning.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-xl border-2 border-red-500 shadow-lg p-2 transform transition duration-300 hover:scale-105 hover:shadow-2xl text-center">
                    <img src="{{ asset('images/scholarship1.jpg') }}" alt="Scholarship 1" class="mx-auto rounded-lg w-full h-72 object-cover">
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-xl border-2 border-red-500 shadow-lg p-2 transform transition duration-300 hover:scale-105 hover:shadow-2xl text-center">
                    <img src="{{ asset('images/scholarship2.jpg') }}" alt="Scholarship 2" class="mx-auto rounded-lg w-full h-72 object-cover">
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-xl border-2 border-red-500 shadow-lg p-2 transform transition duration-300 hover:scale-105 hover:shadow-2xl text-center">
                    <img src="{{ asset('images/scholarship3.jpg') }}" alt="Scholarship 3" class="mx-auto rounded-lg w-full h-72 object-cover">
                </div>
            </div>
        </div>
    </section>



    <!-- Contact Section -->
    <section id="contact" class="pt-24 min-h-screen bg-gray-50">
        <div class="p-6 max-w-6xl mx-auto">
            <h2 class="text-4xl font-bold text-red-700 mb-8">Contact Us</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pablo Borbon Campus -->
                <div class="bg-white rounded-xl border border-red-500 shadow-lg p-6 transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <h3 class="font-bold text-xl text-red-600 mb-2">Pablo Borbon Campus</h3>
                    <p class="text-gray-600 mb-1"><strong>Address:</strong> Scholarship Office – PB, Ground Floor, SSC 2 Bldg., Batangas State University – Pablo Borbon, Rizal Ave., Batangas City, Batangas</p>
                    <p class="text-red-600 mb-1"><strong>Email:</strong> <a href="mailto:scholarship.pb@g.batstate-u.edu.ph" class="hover:underline">scholarship.pb@g.batstate-u.edu.ph</a></p>
                    <p class="text-gray-600"><strong>Tel. no.:</strong> (043) 980-0385 loc. 1834</p>
                </div>

                <!-- Alangilan Campus -->
                <div class="bg-white rounded-xl border border-red-500 shadow-lg p-6 transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <h3 class="font-bold text-xl text-red-600 mb-2">Alangilan Campus</h3>
                    <p class="text-gray-600 mb-1"><strong>Address:</strong> Scholarship Office-Alangilan, Ground Floor, CIT Bldg., Batangas State University-Alangilan, Golden Country Homes, Brgy. Alangilan, Batangas City, Batangas</p>
                    <p class="text-red-600 mb-1"><strong>Email:</strong> <a href="mailto:sfao.alangilan@g.batstate-u.edu.ph" class="hover:underline">sfao.alangilan@g.batstate-u.edu.ph</a></p>
                    <p class="text-gray-600"><strong>Tel. no.:</strong> (043) 425-0139 loc. 2154</p>
                </div>

                <!-- Lipa Campus -->
                <div class="bg-white rounded-xl border border-red-500 shadow-lg p-6 transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <h3 class="font-bold text-xl text-red-600 mb-2">Lipa Campus</h3>
                    <p class="text-gray-600 mb-1"><strong>Address:</strong> Scholarship Office-Lipa, Ground Floor, CECS Bldg., Batangas State University-Lipa, Brgy. Marawoy, Lipa City, Batangas</p>
                    <p class="text-red-600 mb-1"><strong>Email:</strong> <a href="mailto:sfao.lipa@g.batstate-u.edu.ph" class="hover:underline">sfao.lipa@g.batstate-u.edu.ph</a></p>
                    <p class="text-gray-600"><strong>Tel. no.:</strong> (043) 774-2526 loc. 3113 / 3103</p>
                </div>

                <!-- JPLPC Malvar Campus -->
                <div class="bg-white rounded-xl border border-red-500 shadow-lg p-6 transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <h3 class="font-bold text-xl text-red-600 mb-2">JPLPC Malvar Campus</h3>
                    <p class="text-gray-600 mb-1"><strong>Address:</strong> Scholarship Office-Malvar, CECS Bldg., Batangas State University JPLPC Malvar Campus, G. Leviste St., Poblacion, Malvar, Batangas</p>
                    <p class="text-red-600 mb-1"><strong>Email:</strong> <a href="mailto:sfao.malvar@g.batstate-u.edu.ph" class="hover:underline">sfao.malvar@g.batstate-u.edu.ph</a></p>
                    <p class="text-gray-600"><strong>Tel. no.:</strong> (043) 416-0350; 416-0068 loc. 206</p>
                </div>

                <!-- ARASOF Nasugbu Campus -->
                <div class="bg-white rounded-xl border border-red-500 shadow-lg p-6 transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <h3 class="font-bold text-xl text-red-600 mb-2">ARASOF Nasugbu Campus</h3>
                    <p class="text-gray-600 mb-1"><strong>Address:</strong> Scholarship Office-Nasugbu, Batangas State University-ARASOF Nasugbu, Brgy. Bucana, Nasugbu, Batangas</p>
                    <p class="text-red-600 mb-1"><strong>Email:</strong> <a href="mailto:sfao.nasugbu@g.batstate-u.edu.ph" class="hover:underline">sfao.nasugbu@g.batstate-u.edu.ph</a></p>
                    <p class="text-gray-600"><strong>Tel. no.:</strong> (043) 416-0350 loc. 206</p>
                </div>

                <!-- Central Administration -->
                <div class="bg-white rounded-xl border border-red-500 shadow-lg p-6 transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <h3 class="font-bold text-xl text-red-600 mb-2">Central Administration</h3>
                    <p class="text-gray-600 mb-1"><strong>Landline:</strong> (043) 980-0385 loc. 1144</p>
                    <p class="text-gray-600 mb-1"><strong>Mobile:</strong> 09985354992</p>
                    <p class="text-red-600 mb-1"><strong>Email:</strong> <a href="mailto:scholarship.centraloffice@g.batstate-u.edu.ph" class="hover:underline">scholarship.centraloffice@g.batstate-u.edu.ph</a></p>
                    <p class="text-gray-600"><strong>Official Facebook Page:</strong> <a href="https://www.facebook.com/BatStateUScholars" class="text-red-600 hover:underline" target="_blank">facebook.com/BatStateUScholars</a></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    @include('partials.landing-footer')
@endsection

@push('scripts')
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
@endpush
