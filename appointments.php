<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$db = get_db();

$status = $_GET['status'] ?? null;
$alert = null;
if ($status === 'created') {
    $alert = 'Appointment scheduled successfully.';
} elseif ($status === 'updated') {
    $alert = 'Appointment updated successfully.';
} elseif ($status === 'deleted') {
    $alert = 'Appointment removed.';
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
$editAppointment = null;

if ($editId) {
    $stmt = $db->prepare('SELECT * FROM appointments WHERE id = ?');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editAppointment = $result->fetch_assoc() ?: null;
    $stmt->close();
}

$appointments = [];
$result = $db->query('SELECT * FROM appointments ORDER BY appointment_date ASC');
if ($result) {
    $appointments = $result->fetch_all(MYSQLI_ASSOC);
}

$counts = [
    'scheduled' => 0,
    'completed' => 0,
    'cancelled' => 0,
];

foreach ($appointments as $appointment) {
    $statusKey = strtolower(str_replace('-', '', $appointment['status']));
    if (str_contains($statusKey, 'scheduled')) {
        $counts['scheduled']++;
    } elseif (str_contains($statusKey, 'completed')) {
        $counts['completed']++;
    } else {
        $counts['cancelled']++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Appointments | Pet Admin</title>
    <link
      rel="preconnect"
      href="https://fonts.googleapis.com"
      crossorigin
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
    />
    <link rel="stylesheet" href="./styles.css" />
  </head>
  <body>
    <div class="page">
      <aside class="sidebar">
        <div class="logo">
          <div class="logo-icon">
            <svg viewBox="0 0 64 64" aria-hidden="true">
              <circle cx="20" cy="20" r="8" />
              <circle cx="44" cy="20" r="8" />
              <circle cx="20" cy="38" r="8" />
              <circle cx="44" cy="38" r="8" />
              <path d="M32 28c-10 0-18 8-18 18s8 12 18 12 18-2 18-12-8-18-18-18z" />
            </svg>
          </div>
          <div>
            <p class="logo-title">Pet Admin</p>
            <p class="logo-subtitle">Care dashboard</p>
          </div>
        </div>
        <nav class="nav">
          <a class="nav-btn" href="index.php">Dashboard</a>
          <a class="nav-btn" href="pets.php">Pet roster</a>
          <a class="nav-btn active" href="appointments.php">Appointments</a>
          <a class="nav-btn" href="inventory.php">Inventory</a>
        </nav>
        <div class="sidebar-footer">
          <p>Logged in as</p>
          <strong>Dr. Riley Ford</strong>
        </div>
      </aside>
      <main class="content">
        <header class="content-header">
          <div class="hero-copy">
            <p class="eyebrow">Care schedule</p>
            <h1>Manage appointments</h1>
            <p class="lead">
              Schedule wellness checks, vaccine boosters, grooming visits, and follow-ups in a single place.
              Jump to the summary grid to see the day at a glance.
            </p>
            <div class="header-actions">
              <a class="ghost-btn" href="index.php">Back to dashboard</a>
            </div>
          </div>
          <div class="hero-art">
            <div class="hero-glow"></div>
            <img
              src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=800&q=80"
              alt="Vet holding a happy dog"
              loading="lazy"
            />
          </div>
        </header>

        <?php if ($alert): ?>
          <div class="alert alert-info"><?= h($alert) ?></div>
        <?php endif; ?>

        <section class="stats-grid">
          <article class="stat-card">
            <p class="label">Scheduled</p>
            <p class="value"><?= h((string) $counts['scheduled']) ?></p>
            <p class="delta neutral">Upcoming visits</p>
          </article>
          <article class="stat-card">
            <p class="label">Completed</p>
            <p class="value"><?= h((string) $counts['completed']) ?></p>
            <p class="delta positive">Wrapped up</p>
          </article>
          <article class="stat-card">
            <p class="label">Cancelled / No-show</p>
            <p class="value"><?= h((string) $counts['cancelled']) ?></p>
            <p class="delta">Needs attention</p>
          </article>
        </section>

        <section class="panel">
          <div class="panel-head">
            <div>
              <p class="eyebrow"><?= $editAppointment ? 'Edit appointment' : 'Schedule new visit' ?></p>
              <h2>Appointment manager</h2>
            </div>
            <?php if ($editAppointment): ?>
              <a class="ghost-btn" href="appointments.php">Cancel edit</a>
            <?php endif; ?>
          </div>
          <form class="form-grid" action="appointment_save.php" method="POST">
            <?php if ($editAppointment): ?>
              <input type="hidden" name="id" value="<?= h((string) $editAppointment['id']) ?>" />
            <?php endif; ?>
            <label>
              Pet name
              <input
                name="pet_name"
                type="text"
                required
                value="<?= h($editAppointment['pet_name'] ?? '') ?>"
                placeholder="Luna"
              />
            </label>
            <label>
              Owner name
              <input
                name="owner_name"
                type="text"
                value="<?= h($editAppointment['owner_name'] ?? '') ?>"
                placeholder="Maya Sanders"
              />
            </label>
            <label>
              Owner contact
              <input
                name="owner_contact"
                type="tel"
                value="<?= h($editAppointment['owner_contact'] ?? '') ?>"
                placeholder="+1 (555) 010-2255"
              />
            </label>
            <label>
              Appointment date
              <input
                name="appointment_date"
                type="datetime-local"
                required
                value="<?=
                    $editAppointment
                        ? h(date('Y-m-d\TH:i', strtotime($editAppointment['appointment_date'])))
                        : ''
                ?>"
              />
            </label>
            <label>
              Reason
              <input
                name="reason"
                type="text"
                value="<?= h($editAppointment['reason'] ?? '') ?>"
                placeholder="Vaccine booster"
              />
            </label>
            <label>
              Status
              <select name="status" required>
                <?php
                $statuses = ['Scheduled', 'Completed', 'Cancelled', 'No-show'];
                $currentStatus = $editAppointment['status'] ?? 'Scheduled';
                foreach ($statuses as $statusOption): ?>
                  <option value="<?= h($statusOption) ?>" <?= $currentStatus === $statusOption ? 'selected' : '' ?>>
                    <?= h($statusOption) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="full">
              Notes
              <textarea
                name="notes"
                rows="3"
                placeholder="Dietary reminders, medication prep..."
              ><?= h($editAppointment['notes'] ?? '') ?></textarea>
            </label>
            <div class="form-actions">
              <button class="primary-btn" type="submit">
                <?= $editAppointment ? 'Update appointment' : 'Schedule appointment' ?>
              </button>
              <button class="ghost-btn" type="reset">Reset</button>
            </div>
          </form>
        </section>

        <section class="panel">
          <div class="panel-head">
            <div>
              <p class="eyebrow">Summary</p>
              <h2>All appointments</h2>
            </div>
          </div>
          <?php if (!$appointments): ?>
            <p class="empty">No appointments scheduled yet.</p>
          <?php else: ?>
            <div class="cards-grid">
              <?php foreach ($appointments as $appointment): ?>
                <article class="pet-card">
                  <div class="pet-card-header">
                    <div class="avatar"></div>
                    <div>
                      <strong><?= h($appointment['pet_name']) ?></strong>
                      <p class="pet-meta">
                        <?= h($appointment['owner_name'] ?: 'Unknown owner') ?>
                      </p>
                    </div>
                    <span class="badge"><?= h($appointment['status']) ?></span>
                  </div>
                  <p class="timestamp">
                    <?= h(date('M j, Y g:i A', strtotime($appointment['appointment_date']))) ?>
                  </p>
                  <?php if ($appointment['reason']): ?>
                    <p class="pet-notes"><strong>Reason:</strong> <?= h($appointment['reason']) ?></p>
                  <?php endif; ?>
                  <p class="pet-notes"><?= $appointment['notes'] ? h($appointment['notes']) : 'No notes supplied.' ?></p>
                  <div class="actions">
                    <a class="ghost-btn" href="appointments.php?edit=<?= h((string) $appointment['id']) ?>">Edit</a>
                    <form action="appointment_delete.php" method="POST" onsubmit="return confirm('Delete this appointment?');">
                      <input type="hidden" name="id" value="<?= h((string) $appointment['id']) ?>" />
                      <button class="ghost-btn destructive" type="submit">Delete</button>
                    </form>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      </main>
    </div>
    <footer class="site-footer">
      <div class="footer-brand">
        <div class="footer-logo">
          <svg viewBox="0 0 64 64" aria-hidden="true">
            <circle cx="20" cy="20" r="8" />
            <circle cx="44" cy="20" r="8" />
            <circle cx="20" cy="38" r="8" />
            <circle cx="44" cy="38" r="8" />
            <path d="M32 28c-10 0-18 8-18 18s8 12 18 12 18-2 18-12-8-18-18-18z" />
          </svg>
        </div>
        <h3>Pet Admin</h3>
        <p>Smoother schedules, happier pets. Keep teams aligned before, during, and after every visit.</p>
      </div>
      <div class="footer-columns">
        <div class="footer-col">
          <h4>Visit us</h4>
          <p>123 Pawsitive Ave<br />Wellness City, CA 90210</p>
        </div>
        <div class="footer-col">
          <h4>Contact</h4>
          <p>+1 (555) 010-4411</p>
          <a href="mailto:hello@petadmin.com">hello@petadmin.com</a>
        </div>
        <div class="footer-col">
          <h4>Service provider</h4>
          <ul>
            <li>Wellness & dental care</li>
            <li>Behavior consults</li>
            <li>Home visit program</li>
          </ul>
        </div>
      </div>
    </footer>
  </body>
</html>

