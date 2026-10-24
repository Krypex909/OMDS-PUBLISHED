<?php
function admin_header(string $title, array $user): void
{
    $flash = flash_get();
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
  <div class="leftSide">
    <div class="logoDiv">
      <img class="logo" src="../images/OMDS Logo-cropped no word in bottom.svg" alt="">
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
                <span class="text"> Request Inbox </span>
              </button>
            </li>
            <li>
              <button class="buttons download" onclick="loadPage(3)">
                <img class="icons" src="icons/download.svg" alt="" />
                <span class="text"> User Management </span>
              </button>
            </li>
            <li>
              <button class="buttons visitorHistory" onclick="loadPage(4)">
                <img class="icons" src="icons/history.svg" alt="" />
                <span class="text"> Organizations </span>
              </button>
            </li>
            <li>
              <button class="buttons accessControl" onclick="loadPage(6)">
                <img class="icons" src="icons/key.svg" alt="" />
                <span class="text"> Audit Logs </span>
              </button>
            </li>
      </ul>
      <div class="button-container logout">
        <a class="buttons" href="../logout.php">Log Out</a>
      </div>
    </div>
  </div>
  <div class="rightSide">
    <div class="header">
      <h1 class="headerText">OMDS <span id="medical_blue">Admin</span></h1>
    </div>
    <div class="admin-content">
      <p class="admin-who">Signed in as <?php echo e($user['username']); ?></p>
      <?php if ($flash): ?>
        <p class="<?php echo $flash['type'] === 'error' ? 'admin-error' : 'admin-success'; ?>">
          <?php echo e($flash['message']); ?>
        </p>
      <?php endif; ?>
    <?php
}

function admin_footer(): void
{
    ?>
    </div>
  </div>
</div>
</body>
</html>
    <?php
}
