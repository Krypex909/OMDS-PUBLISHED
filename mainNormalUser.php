<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/access.php';

$user = require_login();
if (!can_use_user_shell($user)) {
    redirect(home_path_for($user));
}
$pending = ($user['role'] === 'personnel' && $user['account_status'] === 'pending_personnel');
$denied = ($user['role'] === 'personnel' && $user['account_status'] === 'denied_personnel');
$page = (int) ($_GET['page'] ?? 1);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/admin.css" />
    <meta charset="UTF-8" />
    <title>OMDS User</title>
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
                <span class="text"> Dashboard </span>
              </button>
            </li>
            <li>
              <button class="buttons upload" onclick="loadPage(2)">
                <img class="icons" src="icons/upload.svg" alt="" />
                <span class="text"> Upload Medical File </span>
              </button>
            </li>
            <li>
              <button class="buttons download" onclick="loadPage(3)">
                <img class="icons" src="icons/download.svg" alt="" />
                <span class="text"> Download Files </span>
              </button>
            </li>
            <li>
              <button class="buttons visitorHistory" onclick="loadPage(4)">
                <img class="icons" src="icons/history.svg" alt="" />
                <span class="text"> Visitor History </span>
              </button>
            </li>
            <li>
              <button class="buttons accessControl" onclick="loadPage(6)">
                <img class="icons" src="icons/key.svg" alt="" />
                <span class="text"> Record Access </span>
              </button>
            </li>
            <li>
              <button class="buttons settings" onclick="loadPage(5)">
                <img class="icons" src="icons/settings.svg" alt="" />
                <span class="text"> Settings </span>
              </button>
            </li>
          </ul>
          <div class="button-container logout">
            <button class="buttons" onclick="window.location.href = 'logout.php'">
              <img class="icons" src="icons/logout.svg" alt="" />
              <span class="text">Log Out</span>
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
          <div class="personnel-header-heading">
            <h2>"YOUR MEDICAL INFORMATION, ORGANIZED DIGITALLY"</h2>
          </div>
        </div>

        <?php if ($pending): ?>
          <div class="notice-banner" style="margin: 0 16px;">
            Your authorized personnel application is pending. You can use regular user features. Medical-record access for other people is not available until an administrator approves your application.
          </div>
        <?php elseif ($denied): ?>
          <div class="notice-banner" style="margin: 0 16px;">
            Your authorized personnel application was not approved. You still have a regular user account.
          </div>
        <?php endif; ?>

        <section id="mainContent"></section>
      </div>
    </div>
    <iframe src="Footer.html" class="footer-frame" title="OMDS Footer" scrolling="no"></iframe>

    <script>
      const links = {
        1: "pages/user_dashboard.php",
        2: "pages/user_upload.php",
        3: "pages/user_download.php",
        4: "pages/user_visitor_history.php",
        5: "NormalUser Folder/settings.html",
        6: "pages/user_record_access.php",
      };

      const sidebarOption = {
        1: ".dashboard",
        2: ".upload",
        3: ".download",
        4: ".visitorHistory",
        5: ".settings",
        6: ".accessControl",
      };

      const startPage = <?php echo $page; ?>;

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
          if (!response.ok) {
            throw new Error("Unable to load page: " + response.status);
          }
          const html = await response.text();
          document.getElementById("mainContent").innerHTML = html;
          const selectedOption = document.querySelector(sidebarOption[option]);
          document.querySelectorAll(".highlight").forEach((el) => el.classList.remove("highlight"));
          if (selectedOption) selectedOption.classList.add("highlight");
        } catch (error) {
          console.error(error);
          document.getElementById("mainContent").textContent =
            "Unable to load the requested page. Please try again.";
        }
      }

      loadPage(startPage === 0 ? 1 : startPage);
    </script>
  </body>
</html>
