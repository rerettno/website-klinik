<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Hospital Homepage</title>
</head>
<body class="bg-teal-50">

<header class="bg-teal-300">
    <div class="container mx-auto flex items-center justify-between px-6 py-4">
        <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
            <img src="https://flowbite.com/docs/images/logo.svg" class="h-8" alt="Flowbite Logo" />
            <span class="self-center text-2xl font-semibold whitespace-nowrap text-white">BK HOSPITAL</span>
        </a>
        <nav class="flex items-center">
            <div class="flex items-center space-x-4 ml-6">
                <a href="login.php" class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-400 transition">
                    Login
                </a>
                <a href="register.php" class="border border-white text-white px-4 py-2 rounded hover:bg-teal-400 hover:border-teal-400 transition">
                    Register
                </a>
            </div>
        </nav>
    </div>
</header>


<!-- Hero Section -->
<section class="bg-white text-teal-500  py-20">
    <div class="container mx-auto grid grid-cols-1 md:grid-cols-2 items-center gap-8">
        <!-- Text Section -->
        <div>
            <h1 class="text-4xl font-extrabold mb-4">Caring for Your Health</h1>
            <p class="text-lg mb-6">We provide high-quality healthcare services with the latest medical technology.</p>
            <a href="#" class="bg-teal-500 px-6 py-3 rounded-full text-white font-medium hover:bg-teal-400 transition">
                Book an Appointment
            </a>
        </div>
        <!-- Image Section -->
        <div>
            <img src="https://i.pinimg.com/736x/21/71/40/217140c4e90db3795f53955214fe63fd.jpg" alt="Hospital Illustration" class="w-full">
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto text-center">
        <h2 class="text-3xl font-bold text-teal-600 mb-8">Our Services</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-teal-50 rounded-lg shadow-md p-6 transition hover:shadow-lg">
                <img src="https://cdn-icons-png.flaticon.com/512/3022/3022923.png" alt="Checkup" class="w-16 mx-auto mb-4">
                <h3 class="text-lg font-bold text-teal-700">General Checkup</h3>
                <p class="text-gray-600">Routine health assessments for your well-being.</p>
            </div>
            <div class="bg-teal-50 rounded-lg shadow-md p-6 transition hover:shadow-lg">
                <img src="https://cdn-icons-png.flaticon.com/512/3022/3022883.png" alt="Emergency" class="w-16 mx-auto mb-4">
                <h3 class="text-lg font-bold text-teal-700">Emergency Care</h3>
                <p class="text-gray-600">24/7 urgent medical attention.</p>
            </div>
            <div class="bg-teal-50 rounded-lg shadow-md p-6 transition hover:shadow-lg">
                <img src="https://cdn-icons-png.flaticon.com/512/3022/3022885.png" alt="Specialist" class="w-16 mx-auto mb-4">
                <h3 class="text-lg font-bold text-teal-700">Specialist Consultation</h3>
                <p class="text-gray-600">Expert care from certified specialists.</p>
            </div>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="py-16 bg-teal-50">
    <div class="container mx-auto text-center">
        <h2 class="text-3xl font-bold text-teal-600 mb-8">How It Works</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6">
                <h3 class="text-lg font-bold text-teal-700 mb-2">Step 1: Book an Appointment</h3>
                <p class="text-gray-700">Schedule your visit online or via phone.</p>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-bold text-teal-700 mb-2">Step 2: Consultation</h3>
                <p class="text-gray-700">Meet with our doctors for a thorough checkup.</p>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-bold text-teal-700 mb-2">Step 3: Treatment</h3>
                <p class="text-gray-700">Receive personalized care and follow-up support.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto text-center">
        <h2 class="text-3xl font-bold text-teal-600 mb-8">What Our Patients Say</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-teal-50 rounded-lg shadow-md p-6">
                <p class="text-gray-700 italic">"Excellent care! The doctors and staff are amazing."</p>
                <h4 class="mt-4 font-bold text-teal-700">- John Doe</h4>
            </div>
            <div class="bg-teal-50 rounded-lg shadow-md p-6">
                <p class="text-gray-700 italic">"I feel confident about my health after every visit."</p>
                <h4 class="mt-4 font-bold text-teal-700">- Jane Smith</h4>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-teal-400 text-teal-100 py-6">
    <div class="container mx-auto text-center">
        <p>&copy; 2024 Hospital. All rights reserved.</p>
        <p>Contact us: <a href="mailto:info@BKhospital.com" class="text-teal-300 hover:text-white">info@BKhospital.com</a></p>
    </div>
</footer>

</body>
</html>
