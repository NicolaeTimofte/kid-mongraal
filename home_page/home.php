<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>KiM x FLASHLIGHTS</title>
  <link rel="stylesheet" href="css_files/home.css" />
  <link rel="stylesheet" href="css_files/relaxed-banana.css" />
  <link rel="stylesheet" href="css_files/accidents.css" />
  <link rel="stylesheet" href="css_files/party-and-friends.css" />
  <link rel="stylesheet" href="css_files/footer.css" />
</head>
<body>

  <nav class="topbar">
    <!--KiM logo-->
    <div class="logo">KiM</div>

    <div class="other-actions">
      <a href="documentation.html" class="btn-memberships">DOCUMENTATION</a>
    </div>

    <div class="authentication-actions">
      <!--log in button-->
      <form action="../login.php" method="get" class="authentication-form">
        <button type="submit" class="btn-login">Sign In</button>
      </form>
      
    </div>
  </nav>

  <main class="hero">
    <!--date picker - defaults to today-->
    <div class="date-container">
        <input type="date" id="event-date" name="event-date" class="date-input" value="<?php echo date('Y-m-d'); ?>">
    </div>

    <h1>KID MONITOR</h1>

    <p id="quote" class="quote encrypted">Your child’s safety, simplified</p> <!-- add: Now you know -->
  </main>

  <main><p class="creator">Created by NicuByNicu + <span class="Strix_modifier">KilStrix</span></p></main>

  <main class="MR-INSANE">
  <div class="insane-container">
    <p>
      “An easy-to-use yet powerful companion. KiM translates your child’s real-time location, nearby incidents, and social surroundings
      into a single, intuitive feed—so busy parents can enjoy effortless peace of mind.” 
      —Prof. Dr. Mr. Insane
    </p>

    <img src="photos/andrei+insane.jpg" alt="Prof. Dr. Mr. Insane" class="insane-photo">
  </div>

  <section id="hero-message" class="hero-message">
    <h2>Get a complete picture of your child’s life</h2>

    <p>No other tracker gives you a more comprehensive view of your kid's day to day life – and tells you how to improve it.</p>
  </section>

  <div class="feature-section">

    <img src="photos/Nana_Nana_Hammock.webp" alt="Chilling Peely" class="banana-inferno">

    <div class="feature-box">
      <ul id="features-list" class="highlight-list">
        <li>Stay relaxed</li>
        <li>Let us do the work</li>
        <li>All info just 1 click away</li>
        <li>No other wearables needed</li>
      </ul>
    </div>

  </div>

  <section id="hero-message2" class="hero-message">
    <h2>Always see if the danger is close</h2>

    <p>The best jumps are the ones you're not sure you can make</p>
  </section>

  <div class="feature-section2">

    <div class="feature-box2">
      <ul id="features-list2" class="highlight-list">
        <li>Visualize on map accidents reported by others</li>
        <li>Get alerts when they wander too far</li>
        <li>Instant notifications for nearby dangers</li>
        <li>Always know their distance to nearest accident</li>
      </ul>
    </div>

    <img src="photos/battle-royale.webp" alt="Concept art for battle royale" class="accidents">

  </div>

  <section id="hero-message3" class="hero-message">
    <h2>Know who they’re with, wherever they go</h2>

    <p>KiM maps your child’s social circle in real time—so you can see exactly
       who they’re spending time with and stay confidently in the loop.</p>
  </section>

  <div class="feature-section">

    <img src="photos/party.webp" alt="Best party ever" class="friends">

    <div class="feature-box3">
      <ul id="features-list3" class="highlight-list">
        <li>Visualize on map accidents reported by others</li>
        <li>Get alerts when they wander too far</li>
        <li>Instant notifications for nearby dangers</li>
        <li>Always know their distance to nearest accident</li>
      </ul>
    </div>

  </div>

</main>

  <footer class="site-footer">
    <div class="footer-container">

      <div class="footer-text">
        <p>Contact Info:</p>
        <ol>
          <li>Email: nicu.timofte04@yahoo.com, sebastian.lucanu@gmail.com, kim7support@insane.com</li>
          <li>Instagram: nicu_rege, kilstrix</li>
          <li>Chess.com: NicuTheGod, alexutzu2003</li>
          <li>Youtube: KilStrix</li>
        </ol>
        <p>Thank you for visiting our website!</p>
      </div>

        <img src="photos/gaara.gif" alt="Loading animation" class="my-gif">

    </div>

</footer>
  

  <script src="js_scripts/cycle-list.js"></script>
  <script src="js_scripts/quote_animation.js"></script>
  <script src="js_scripts/headliner.js"></script>
</body>
</html>
