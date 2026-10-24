<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

start_omds_session();
if (current_user()) {
    redirect(home_path_for(current_user()));
}
$flash = flash_get();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create OMDS Account</title>
    <link rel="stylesheet" href="css/signup.css" />
  </head>
  <body>
    <div class="main-area">
      <header class="page-header">
        <h1>Online <span id="medical_blue">Medical</span> Data Storage</h1>
      </header>
      <p class="page-tagline">"YOUR MEDICAL INFORMATION, ORGANIZED DIGITALLY"</p>

      <div class="signup-container">
        <div class="signup-card">
          <h2>Create an Account</h2>
          <?php if ($flash): ?>
            <p class="<?php echo $flash['type'] === 'error' ? 'form-error' : 'form-success'; ?>">
              <?php echo e($flash['message']); ?>
            </p>
          <?php endif; ?>

          <form action="register.php" method="post" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <div class="form-group">
              <label for="accountType">Account Type</label>
              <select id="accountType" name="accountType" required>
                <option value="User Account Registration" selected>
                  User Account Registration
                </option>
                <option value="Authorized Personnel Account Registration">
                  Authorized Personnel Account Registration
                </option>
              </select>
            </div>

            <div class="form-group">
              <label for="firstName">Full Name</label>
              <div class="name-fields">
                <input type="text" id="firstName" name="firstName" placeholder="First Name" required />
                <input type="text" id="middleName" name="middleName" placeholder="Middle Name" required />
                <input type="text" id="lastName" name="lastName" placeholder="Last Name" required />
              </div>
            </div>

            <div class="form-group">
              <label for="suffix">Suffix (optional)</label>
              <input type="text" id="suffix" name="suffix" placeholder="Jr., III, etc." />
            </div>

            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" id="username" name="username" placeholder="Letters, numbers, dots, underscores" required maxlength="50" />
            </div>

            <div class="form-group">
              <label for="address">Address</label>
              <textarea id="address" name="address" placeholder="Complete address" required></textarea>
            </div>

            <div class="form-group">
              <label for="email">Gmail Address</label>
              <input type="email" id="email" name="email" placeholder="you@gmail.com" required />
            </div>

            <div class="form-group">
              <label for="bloodType">Blood Type</label>
              <select id="bloodType" name="bloodType" required>
                <option value="">Select Blood Type</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="unknown">Unknown</option>
              </select>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <div class="password-field">
                <input
                  type="password"
                  id="password"
                  name="password"
                  placeholder="At least 8 characters"
                  minlength="8"
                  required
                />
                <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password" aria-pressed="false">
                  <svg class="icon-eye" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                  <svg class="icon-eye-off" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.9 10.9 0 0 1 12 19C5 19 1 12 1 12a18.5 18.5 0 0 1 5.06-5.94" />
                    <path d="M9.9 4.24A10.9 10.9 0 0 1 12 5c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19" />
                    <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                    <line x1="1" y1="1" x2="23" y2="23" />
                  </svg>
                </button>
              </div>
            </div>

            <fieldset class="authorized-fields" id="authorizedFields" hidden disabled>
              <div class="form-group">
                <label for="companyName">Corporate or organization name</label>
                <input type="text" id="companyName" name="companyName" placeholder="Your organization" required />
              </div>
              <div class="form-group">
                <label for="specialization">Specialization</label>
                <input type="text" id="specialization" name="specialization" placeholder="e.g. Internal Medicine" required />
              </div>
              <div class="form-group">
                <label for="professionalRole">Professional role</label>
                <input type="text" id="professionalRole" name="professionalRole" placeholder="e.g. Physician" required />
              </div>
              <div class="form-group">
                <label for="companyId">Professional identification number</label>
                <input type="text" id="companyId" name="companyId" placeholder="License or company ID number" required />
              </div>
              <div class="form-group">
                <label for="companyAddress">Organization address</label>
                <input type="text" id="companyAddress" name="companyAddress" placeholder="Organization address" required />
              </div>
              <div class="form-group">
                <label for="companyContact">Organization contact information</label>
                <input type="text" id="companyContact" name="companyContact" placeholder="Phone number or company email" required />
              </div>
              <div class="form-group">
                <label for="companyIdFile">Upload Company / Professional ID</label>
                <div class="file-upload">
                  <label for="companyIdFile" class="file-btn">Choose File</label>
                  <span id="fileName">No file chosen (JPG, PNG or PDF)</span>
                </div>
                <input type="file" id="companyIdFile" name="companyIdFile" class="visually-hidden" accept=".jpg,.jpeg,.png,.pdf" required />
              </div>
            </fieldset>

            <div class="checkbox-group">
              <input type="checkbox" id="terms" name="terms" required />
              <label for="terms">I agree to the Terms and Conditions</label>
            </div>

            <div>
              <button type="submit" class="submit-btn">Sign Up</button>
            </div>

            <div class="login-link">
              Already have an account?
              <a href="login.php">Log In</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      const accountType = document.getElementById("accountType");
      const authorizedFields = document.getElementById("authorizedFields");
      const companyIdFile = document.getElementById("companyIdFile");
      const fileName = document.getElementById("fileName");
      const password = document.getElementById("password");
      const togglePassword = document.getElementById("togglePassword");

      accountType.addEventListener("change", function () {
        const isAuthorized =
          this.value === "Authorized Personnel Account Registration";
        authorizedFields.hidden = !isAuthorized;
        authorizedFields.disabled = !isAuthorized;
      });

      companyIdFile.addEventListener("change", function () {
        fileName.textContent = this.files.length
          ? this.files[0].name
          : "No file chosen (JPG, PNG or PDF)";
      });

      togglePassword.addEventListener("click", function () {
        const isHidden = password.type === "password";
        password.type = isHidden ? "text" : "password";
        this.classList.toggle("is-visible", isHidden);
        this.setAttribute("aria-pressed", isHidden);
        this.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
      });
    </script>
  </body>
</html>
