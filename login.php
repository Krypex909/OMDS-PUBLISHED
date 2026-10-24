<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

start_omds_session();

if (current_user()) {
    redirect(home_path_for(current_user()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = clean_text($_POST['username'] ?? '', 50);
    $accountId = preg_replace('/\D/', '', $_POST['account_id'] ?? '');
    $password = $_POST['password'] ?? '';

    $generic = 'Incorrect account details.';

    if ($username === '' || strlen($accountId) !== 10 || $password === '') {
        flash_set('error', $generic);
        redirect('login.php');
    }

    try {
        $stmt = db()->prepare(
            'SELECT u.*, pp.review_status
             FROM users u
             LEFT JOIN personnel_profiles pp ON pp.user_id = u.id
             WHERE u.username = ? AND u.public_user_id = ?
             LIMIT 1'
        );
        $stmt->execute([$username, $accountId]);
        $user = $stmt->fetch();
    } catch (Throwable $e) {
        flash_set('error', 'The system is not ready. Run install.php first.');
        redirect('login.php');
    }

    if (!$user || !password_verify($password, $user['password_hash'])) {
        flash_set('error', $generic);
        redirect('login.php');
    }

    if (
        $user['account_status'] === 'inactive'
        || !empty($user['is_disabled'])
        || !empty($user['deleted_at'])
    ) {
        flash_set('error', 'This account is disabled.');
        redirect('login.php');
    }

    if ($user['role'] === 'organization' && $user['account_status'] !== 'active') {
        flash_set('error', 'This organization account is not active yet.');
        redirect('login.php');
    }

    login_user($user);
    audit_log(db(), (int) $user['id'], 'login', 'user', (string) $user['id'], 'Successful login');
    redirect(home_path_for($user));
}

$flash = flash_get();
?>
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8" />
    <title>OMDS Login</title>
    <link rel="stylesheet" href="css/login.css" />
  </head>
  <body>
    <div class="container">
      <div class="logo-container">
        <img class="logo" src="images/OMDS Logo-cropped.svg" alt="" />
      </div>

      <div class="big-container">
        <p class="txt">
          Securely upload your important medical records with
          <span class="bluetxt">Online Medical Data Storage</span>
        </p>
        <img
          class="graphic-design"
          src="images/Firefly_RemoveBackground.png"
          alt=""
        />
      </div>

      <form class="info-box" method="post" action="login.php" autocomplete="on">
        <?php echo csrf_field(); ?>
        <?php if ($flash): ?>
          <div class="form-banner <?php echo e($flash['type']); ?>">
            <?php echo e($flash['message']); ?>
          </div>
        <?php endif; ?>

        <div class="input-grid">
          <p>Username</p>
          <input class="input-box" type="text" name="username" placeholder="User name" required maxlength="50" />
        </div>

        <div class="input-grid">
          <p>OMDS Account ID</p>
          <div class="id-row">
            <input
              class="input-box"
              type="text"
              id="accountId"
              name="account_id"
              inputmode="numeric"
              maxlength="10"
              pattern="[0-9]{10}"
              placeholder="10-digit Account ID"
              required
            />
            <button
              type="button"
              class="qr-btn"
              id="qrScanBtn"
              aria-label="Scan QR code"
              title="Scan QR code"
            >
              <img src="icons/qr_code_scanner.svg" alt="" />
            </button>
          </div>
          <p class="id-hint">Type your ID or tap the QR icon to scan it. Password is still required.</p>
        </div>

        <div class="input-grid">
          <p>Password</p>
          <input class="input-box" type="password" name="password" placeholder="●●●●●●●●" required />
        </div>

        <div class="button-grid">
          <a href="SignUp.php">
            <div><button class="button" type="button">Sign up</button></div>
          </a>
          <div>
            <button class="button" type="submit">Log in</button>
          </div>
        </div>
      </form>
    </div>

    <div class="scanner-modal" id="scannerModal" hidden>
      <div class="scanner-box">
        <h3>Scan your OMDS QR Code</h3>
        <video id="scannerVideo" playsinline muted></video>
        <canvas id="scannerCanvas" hidden></canvas>
        <p id="scannerStatus" class="scanner-status"></p>
        <button type="button" class="button" id="closeScanner">Cancel</button>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.min.js"></script>
    <script>
      const accountId = document.getElementById("accountId");
      const qrScanBtn = document.getElementById("qrScanBtn");
      const scannerModal = document.getElementById("scannerModal");
      const scannerVideo = document.getElementById("scannerVideo");
      const scannerCanvas = document.getElementById("scannerCanvas");
      const scannerStatus = document.getElementById("scannerStatus");
      const closeScanner = document.getElementById("closeScanner");

      let stream = null;
      let scanning = false;

      accountId.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, "");
      });

      async function openScanner() {
        scannerModal.hidden = false;
        scannerStatus.textContent = "Starting camera...";

        try {
          stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "environment" },
          });
          scannerVideo.srcObject = stream;
          await scannerVideo.play();
          scannerStatus.textContent = "Point the camera at your QR code";
          scanning = true;
          requestAnimationFrame(scanFrame);
        } catch (error) {
          scannerStatus.textContent =
            "Camera not available. Allow camera access or type the ID instead.";
        }
      }

      function stopScanner() {
        scanning = false;
        if (stream) {
          stream.getTracks().forEach((track) => track.stop());
          stream = null;
        }
        scannerVideo.srcObject = null;
        scannerModal.hidden = true;
      }

      function scanFrame() {
        if (!scanning) return;

        if (scannerVideo.readyState === scannerVideo.HAVE_ENOUGH_DATA) {
          const width = scannerVideo.videoWidth;
          const height = scannerVideo.videoHeight;
          scannerCanvas.width = width;
          scannerCanvas.height = height;

          const context = scannerCanvas.getContext("2d", {
            willReadFrequently: true,
          });
          context.drawImage(scannerVideo, 0, 0, width, height);
          const image = context.getImageData(0, 0, width, height);
          const code = jsQR(image.data, width, height);

          if (code) {
            const match = code.data.match(/(?<!\d)\d{10}(?!\d)/);
            if (match) {
              accountId.value = match[0];
              stopScanner();
              return;
            }
            scannerStatus.textContent =
              "That QR code does not contain a valid 10-digit OMDS Account ID.";
          }
        }

        requestAnimationFrame(scanFrame);
      }

      qrScanBtn.addEventListener("click", openScanner);
      closeScanner.addEventListener("click", stopScanner);

      scannerModal.addEventListener("click", function (event) {
        if (event.target === scannerModal) stopScanner();
      });
      document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !scannerModal.hidden) stopScanner();
      });
    </script>
  </body>
</html>
