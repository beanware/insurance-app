<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);  // Check if user is logged in (assuming 'user_id' is set upon login)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insure | Protect What Matters Most</title>
    <style>
        /* CSS Reset */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Base Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
        }

        a {
            color: #0056b3;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #003d80;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            padding: 20px 0;
        }

        /* Navigation Bar */
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 0;
        }

        .navbar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0056b3;
        }

        .navbar .nav-links {
            display: flex;
            gap: 20px;
        }

        .navbar .nav-links a {
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .navbar .nav-links a:hover {
            background-color: #f0f0f0;
        }

        /* Header Section */
        .header {
            text-align: center;
            padding: 60px 20px;
            background-image: url('https://images.unsplash.com/photo-1600342416458-414b0e0ae9e4?w=1200&auto=format&fit=crop&q=80');
            background-size: cover;
            background-position: center;
            color: #fff;
            position: relative;
            border-radius: 8px;
            margin-bottom: 40px;
        }

        .header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            border-radius: 8px;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .header p {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        /* Insurance Plans */
        .plans {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .plan {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .plan:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 10px rgba(0,0,0,0.15);
        }

        .plan img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .plan-content {
            padding: 20px;
        }

        .plan-title {
            font-size: 1.5rem;
            color: #0056b3;
            margin-bottom: 10px;
        }

        .plan-details, .plan-benefits {
            list-style-type: disc;
            margin-left: 20px;
            margin-bottom: 15px;
        }

        .plan-benefits li {
            margin-bottom: 8px;
        }

        .plan .button {
            display: inline-block;
            background-color: #0056b3;
            color: #fff;
            padding: 10px 20px;
            border-radius: 4px;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .plan .button:hover {
            background-color: #003d80;
        }

        /* Advertisement Section */
        .advertisement {
            margin: 60px 0;
            text-align: center;
        }

        .advertisement h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
        }

        .advertisement-images {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .advertisement-images img {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .advertisement-images img:hover {
            transform: scale(1.05);
        }

        /* Call-to-Action Section */
        .cta {
            background-color: #0056b3;
            color: #fff;
            padding: 40px 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 60px;
        }

        .cta h2 {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .cta ul {
            list-style-type: none;
            margin-bottom: 20px;
        }

        .cta ul li {
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .cta .cta-button {
            background-color: #fff;
            color: #0056b3;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .cta .cta-button:hover {
            background-color: #f0f0f0;
            color: #003d80;
        }

        /* Footer */
        .footer {
            background-color: #333;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            border-top: 4px solid #0056b3;
        }

        .footer a {
            color: #fff;
            margin: 0 10px;
        }

        .footer a:hover {
            color: #0056b3;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .header p {
                font-size: 1rem;
            }

            .navbar .nav-links {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .advertisement-images {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container flex justify-between items-center">
            <div class="logo">
                <a href="/">Insure</a>
            </div>
            <div class="nav-links">
                <?php if ($loggedIn): ?>
                    <a href="my_plans.php" class="button">View My Plans</a>
                    <a href="admin_dashboard.php" class="button">Claims</a>
                    <a href="logout.php" class="button">Logout</a>
                <?php else: ?>
                    <a href="user_login.php" class="button">Login</a>
                    <a href="user_registration.php" class="button">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <header class="header">
        <div class="header-content">
            <h1>Insure</h1>
            <p>Protect What Matters Most to You!</p>
            <p>At Insure, we offer a range of comprehensive insurance plans tailored to fit your needs and budget.</p>
        </div>
    </header>

    <!-- Insurance Plans -->
    <section class="container">
        <div class="plans">
            <!-- Health Insurance Plan -->
            <div class="plan">
                <img src="https://images.unsplash.com/photo-1588776814546-1a93f5b5a8a2?w=600&auto=format&fit=crop&q=60" alt="Health Insurance">
                <div class="plan-content">
                    <h2 class="plan-title">Health Insurance</h2>
                    <ul class="plan-details">
                        <li>Coverage Amount: KES 500,000</li>
                        <li>Premium Amount: KES 20,000 per year</li>
                        <li>Deductible Amount: KES 5,000</li>
                    </ul>
                    <p>Why Choose This Plan?</p>
                    <ul class="plan-benefits">
                        <li>Comprehensive Coverage: Hospital stays, surgeries, and preventive care.</li>
                        <li>Network of Hospitals: Access to top healthcare providers.</li>
                        <li>24/7 Customer Support: Assistance whenever you need it.</li>
                    </ul>
                    <!-- Prevent sign-up if not logged in -->
                    <a href="<?php echo $loggedIn ? 'policy_creation.php?plan=health' : 'user_login.php'; ?>" class="button">
                        Sign Up for Health Plan
                    </a>
                </div>
            </div>

            <!-- Motor Insurance Plan -->
            <div class="plan">
                <img src="https://images.unsplash.com/photo-1549924231-f129b911e442?w=600&auto=format&fit=crop&q=60" alt="Motor Insurance">
                <div class="plan-content">
                    <h2 class="plan-title">Motor Insurance</h2>
                    <ul class="plan-details">
                        <li>Coverage Amount: KES 1,000,000</li>
                        <li>Premium Amount: KES 25,000 per year</li>
                        <li>Deductible Amount: KES 10,000</li>
                    </ul>
                    <p>Why Choose This Plan?</p>
                    <ul class="plan-benefits">
                        <li>Full Coverage: Protect against theft, accidents, and third-party liabilities.</li>
                        <li>Roadside Assistance: 24/7 support for emergencies.</li>
                        <li>Flexible Payment Options: Choose a plan that fits your budget.</li>
                    </ul>
                    <!-- Prevent sign-up if not logged in -->
                    <a href="<?php echo $loggedIn ? 'policy_creation.php?plan=motor' : 'user_login.php'; ?>" class="button">
                        Sign Up for Motor Plan
                    </a>
                </div>
            </div>

            <!-- Home Insurance Plan -->
            <div class="plan">
                <img src="https://images.unsplash.com/photo-1560185127-6c9c46c55e1b?w=600&auto=format&fit=crop&q=60" alt="Home Insurance">
                <div class="plan-content">
                    <h2 class="plan-title">Home Insurance</h2>
                    <ul class="plan-details">
                        <li>Coverage Amount: KES 2,000,000</li>
                        <li>Premium Amount: KES 15,000 per year</li>
                        <li>Deductible Amount: KES 7,500</li>
                    </ul>
                    <p>Why Choose This Plan?</p>
                    <ul class="plan-benefits">
                        <li>Protect Your Home: Coverage for fire, theft, and natural disasters.</li>
                        <li>Liability Protection: Safeguard against accidents occurring on your property.</li>
                        <li>Personal Belongings Coverage: Protects your valuables inside the home.</li>
                    </ul>
                    <!-- Prevent sign-up if not logged in -->
                    <a href="<?php echo $loggedIn ? 'policy_creation.php?plan=home' : 'user_login.php'; ?>" class="button">
                        Sign Up for Home Plan
                    </a>
                </div>
            </div>

            <!-- Life Insurance Plan -->
            <div class="plan">
                <img src="https://images.unsplash.com/photo-1607746882042-944635dfe10e?w=600&auto=format&fit=crop&q=60" alt="Life Insurance">
                <div class="plan-content">
                    <h2 class="plan-title">Life Insurance</h2>
                    <ul class="plan-details">
                        <li>Coverage Amount: KES 3,000,000</li>
                        <li>Premium Amount: KES 30,000 per year</li>
                        <li>Deductible Amount: KES 15,000</li>
                    </ul>
                    <p>Why Choose This Plan?</p>
                    <ul class="plan-benefits">
                        <li>Financial Security: Provides for your loved ones in case of unforeseen events.</li>
                        <li>Flexible Terms: Customize your coverage according to your needs.</li>
                        <li>Investment Opportunities: Build savings alongside your insurance coverage.</li>
                    </ul>
                    <!-- Prevent sign-up if not logged in -->
                    <a href="<?php echo $loggedIn ? 'policy_creation.php?plan=life' : 'user_login.php'; ?>" class="button">
                        Sign Up for Life Plan
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Advertisement Section -->
    <section class="advertisement container">
        <h2>Current Promotions</h2>
        <div class="advertisement-images">
            <img src="https://images.unsplash.com/photo-1558898268-92ae44e7670e?w=600&auto=format&fit=crop&q=60" alt="Promotion 1">
            <img src="https://plus.unsplash.com/premium_photo-1681224438035-e8e0ec5e6021?w=600&auto=format&fit=crop&q=60" alt="Promotion 2">
            <img src="https://images.unsplash.com/photo-1604344265121-204d7a575e6b?w=600&auto=format&fit=crop&q=60" alt="Promotion 3">
            <img src="https://images.unsplash.com/photo-1588854337117-8892bd245b3d?w=600&auto=format&fit=crop&q=60" alt="Promotion 4">
        </div>
    </section>

    <!-- Call-to-Action Section -->
    <section class="cta container">
        <h2>✨ Why Insure with Us?</h2>
        <ul>
            <li>Flexible Coverage: Choose a plan that fits your lifestyle and assets.</li>
            <li>Quick Claims Process: Hassle-free claims to get you back on track.</li>
            <li>Affordable Premiums: Competitive rates that won’t break the bank.</li>
            <li>24/7 Customer Support: Always here to assist you.</li>
        </ul>
        <p>Ready to Protect What Matters?</p><br>
        <a href="user_registration.php" class="cta-button">👉 Sign Up Now!</a>
        <br><br><p>Already have an account? <a href="user_login.php" class="button">Log In</a></p>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Beanware. All Rights Reserved.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook"><img src="https://img.icons8.com/ios-filled/24/ffffff/facebook.png" alt="Facebook"></a>
                <a href="#" aria-label="Twitter"><img src="https://img.icons8.com/ios-filled/24/ffffff/twitter.png" alt="Twitter"></a>
                <a href="#" aria-label="LinkedIn"><img src="https://img.icons8.com/ios-filled/24/ffffff/linkedin.png" alt="LinkedIn"></a>
                <a href="#" aria-label="Instagram"><img src="https://img.icons8.com/ios-filled/24/ffffff/instagram-new.png" alt="Instagram"></a>
            </div>
        </div>
    </footer>
</body>
</html>
