<?php
function admin_is_partial_request(): bool
{
    return ($_GET['partial'] ?? '') === '1';
}

function admin_header(string $title, array $user): void
{
    $flash = flash_get();
    if (admin_is_partial_request()) {
        if ($flash) {
            ?>
            <p class="<?php echo $flash['type'] === 'error' ? 'admin-error' : 'admin-success'; ?>">
              <?php echo e($flash['message']); ?>
            </p>
            <?php
        }
        return;
    }
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?></title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/NormalUser/dashCSS2.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="upper">
  <div style="position: fixed;
  bottom: 0;
  top: 0;
  width: 250px;" class="leftSide">
    <div class="logoDiv">
      <img class="logo" src="../images/OMDS Logo-cropped no word in bottom.svg" alt="">
    </div>
    <div class="left-navigation">
      <ul class="button-container">
            <li>
              <button class="buttons dashboard" onclick="loadPage(1)">
                <img class="icons" src="../icons/dashboard.svg" alt="" />
                <span class="text"> Dashboard </span>
              </button>
            </li>
            <li>
              <button class="buttons requestInbox" onclick="loadPage(2)">
                <img class="icons" src="../icons/inbox.svg" alt="" />
                <span class="text"> Request Inbox </span>
              </button>
            </li>
            <li>
              <button class="buttons userManagement" onclick="loadPage(3)">
                <img class="icons" src="../icons/users.svg" alt="" />
                <span class="text"> User Management </span>
              </button>
            </li>
            <li>
              <button class="buttons organizations" onclick="loadPage(4)">
                <img class="icons" src="../icons/org.svg" alt="" />
                <span class="text"> Organizations </span>
              </button>
            </li>
            <li>
              <button class="buttons auditLogs" onclick="loadPage(5)">
                <img class="icons" src="../icons/history.svg" alt="" />
                <span class="text"> Audit Logs </span>
              </button>
            </li>
      </ul>
          <div class="button-container logout">
            <button class="buttons" onclick="window.location.href = '../logout.php'">
              <img class="icons" src="../icons/logout.svg" alt="" />
              <span class="text">Log Out</span>
            </button>
          </div>
    </div>
  </div>
  <div style="margin-left: 250px;" class="rightSide">
    <div class="header">
      <h1 class="headerText">OMDS <span id="medical_blue">Admin</span></h1>
    </div>
    <div class="menu-box">
      <button class="menu-button" type="button" onclick="toggleSidebar()" aria-label="Toggle navigation menu">
        <img class="menu-icon" src="../icons/menu.svg" alt="" />
      </button>
    </div>
    <div class="admin-content">
      
      <?php if ($flash): ?>
        <p class="<?php echo $flash['type'] === 'error' ? 'admin-error' : 'admin-success'; ?>">
          <?php echo e($flash['message']); ?>
        </p>
      <?php endif; ?>
      <section id="mainContent">
    <?php
}

function admin_footer(): void
{
    if (admin_is_partial_request()) {
        return;
    }
    ?>
      </section>
    </div>
  </div>
</div>
<script>
  const links = {
    1: "dashboard.php",
    2: "inbox.php",
    3: "users.php",
    4: "organizations.php",
    5: "audit.php",
  };
  const sidebarOption = {
    1: ".dashboard",
    2: ".requestInbox",
    3: ".userManagement",
    4: ".organizations",
    5: ".auditLogs",
  };
  const pageOptions = {
    "dashboard.php": 1,
    "inbox.php": 2,
    "application.php": 2,
    "users.php": 3,
    "user_action.php": 3,
    "organizations.php": 4,
    "organization_members.php": 4,
    "audit.php": 5,
  };

  function updateSidebar(pathname) {
    const pageName = pathname.split("/").pop();
    const option = pageOptions[pageName] || 1;
    document.querySelectorAll(".buttons.highlight").forEach((button) => {
      button.classList.remove("highlight");
    });
    document.querySelector(sidebarOption[option]).classList.add("highlight");
  }

  async function loadUrl(target, addHistory = true) {
    const pageUrl = new URL(target, window.location.href);
    const requestUrl = new URL(pageUrl.href);
    requestUrl.searchParams.set("partial", "1");

    try {
      const response = await fetch(requestUrl.href);
      if (!response.ok) {
        throw new Error("Unable to load page: " + response.status);
      }
      const html = await response.text();
      document.getElementById("mainContent").innerHTML = html;
      updateSidebar(pageUrl.pathname);
      if (addHistory) {
        window.history.pushState({}, "", pageUrl.href);
      }
    } catch (error) {
      console.error(error);
      document.getElementById("mainContent").textContent =
        "Unable to load the requested page. Please try again.";
    }
  }

  function loadPage(option) {
    if (links[option]) {
      loadUrl(links[option]);
    }
  }

  function toggleSidebar() {
    document.querySelector(".leftSide").classList.toggle("hidden");
  }

  document.getElementById("mainContent").addEventListener("click", function (event) {
    if (!(event.target instanceof Element)) {
      return;
    }
    const anchor = event.target.closest("a");
    if (
      !anchor ||
      anchor.target === "_blank" ||
      event.defaultPrevented ||
      event.button !== 0 ||
      event.ctrlKey ||
      event.metaKey ||
      event.shiftKey ||
      event.altKey
    ) {
      return;
    }

    const destination = new URL(anchor.href);
    const adminDirectory = new URL(".", window.location.href).pathname;
    if (
      destination.origin !== window.location.origin ||
      !destination.pathname.startsWith(adminDirectory) ||
      !destination.pathname.endsWith(".php")
    ) {
      return;
    }

    event.preventDefault();
    loadUrl(destination.href);
  });

  document.addEventListener("click", function (event) {
    if (window.matchMedia("(max-width: 600px)").matches) {
      const sidebar = document.querySelector(".leftSide");
      const menuButton = document.querySelector(".menu-button");
      if (
        sidebar &&
        menuButton &&
        !sidebar.contains(event.target) &&
        !menuButton.contains(event.target)
      ) {
        sidebar.classList.remove("hidden");
      }
    }
  });

  window.addEventListener("popstate", function () {
    loadUrl(window.location.href, false);
  });

  updateSidebar(window.location.pathname);
</script>
</body>
</html>
    <?php
}
