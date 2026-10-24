<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/access.php';

$user = require_approved_personnel();
$page = (int) ($_GET['page'] ?? 1);
$token = clean_text($_GET['t'] ?? '', 64);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/admin.css" />
    <meta charset="UTF-8" />
    <title>OMDS Personnel</title>
  </head>
  <body>
    <div class="upper">
      <div class="leftSide">
        <div class="logoDiv">
          <img class="logo" src="images/OMDS Logo-cropped no word in bottom.svg" alt="" />
        </div>
        <div class="left-navigation">
          <ul class="button-container">
            <li>
              <button class="buttons dashboard" onclick="loadPage(1)">
                <img class="icons" src="icons/dashboard.svg" alt="" />
                <span class="text">Dashboard</span>
              </button>
            </li>
            <li>
              <button class="buttons download" onclick="loadPage(2)">
                <img class="icons" src="icons/download.svg" alt="" />
                <span class="text">Downloaded Files</span>
              </button>
            </li>
            <li>
              <button class="buttons accessHistory" onclick="loadPage(3)">
                <img class="icons" src="icons/history.svg" alt="" />
                <span class="text">Access History</span>
              </button>
            </li>
            <li>
              <button class="buttons settings" onclick="loadPage(4)">
                <img class="icons" src="icons/settings.svg" alt="" />
                <span class="text">Settings</span>
              </button>
            </li>
          </ul>
          <div class="button-container">
            <button class="logout-buttons" onclick="window.location.href = 'logout.php'">
              <img class="icons" src="icons/logout.svg" alt="" />
              Log Out
            </button>
          </div>
        </div>
      </div>

      <div class="rightSide">
        <div class="header">
          <h1 class="headerText">
            Online <span id="medical_blue">Medical</span> Data Storage
          </h1>
        </div>

        <div class="middle-area">
          <div class="menu-box">
            <div class="menu">
              <button class="menu-button" onclick="toggleSidebar()" aria-label="Menu">
                <img class="menu-icon" src="icons/menu.svg" alt="" />
              </button>
            </div>
          </div>
          <form class="search-group" method="get" action="scan.php">
            <input class="search-box" type="text" name="username" placeholder="Search Username" aria-label="Search Username" />
            <input class="search-box" type="text" name="account_id" placeholder="User ID" aria-label="User ID" maxlength="10" />
            <a class="qr-button" href="scan.php" aria-label="Open secure scan page">
              <img class="search-icon" src="icons/qr_code_scanner.svg" alt="" />
            </a>
          </form>
        </div>
        <section id="mainContent"></section>
      </div>
    </div>
    <iframe src="Footer.html" class="footer-frame" title="OMDS Footer" scrolling="no"></iframe>

    <script>
      const links = {
        1: "pages/personnel_dashboard.php",
        2: "pages/personnel_files.php",
        3: "pages/personnel_access_history.php",
        4: "Personnel Folder/settings_personnel.html",
      };
      const sidebarOption = {
        1: ".dashboard",
        2: ".download",
        3: ".accessHistory",
        4: ".settings",
      };

      document.addEventListener("click", function (event) {
        if (window.matchMedia("(width < 600px)").matches) {
          const sidebar = document.querySelector(".leftSide");
          const menuButton = document.querySelector(".menu-button");
          if (!sidebar.contains(event.target) && !menuButton.contains(event.target)) {
            sidebar.classList.remove("hidden");
          }
        }
      });
      async function toggleSidebar() {
        document.querySelector(".leftSide").classList.toggle("hidden");
      }
      async function loadPage(option) {
        try {
          const response = await fetch(links[option]);
          if (!response.ok) throw new Error("Unable to load page: " + response.status);
          document.getElementById("mainContent").innerHTML = await response.text();
          const selectedOption = document.querySelector(sidebarOption[option]);
          document.querySelectorAll(".highlight").forEach((el) => el.classList.remove("highlight"));
          if (selectedOption) selectedOption.classList.add("highlight");
        } catch (error) {
          console.error(error);
          document.getElementById("mainContent").textContent =
            "Unable to load the requested page. Please try again.";
        }
      }
      loadPage(<?php echo $page ?: 1; ?>);
    </script>
  </body>
</html>
