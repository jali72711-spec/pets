<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$status = $_GET['status'] ?? null;
$alert = match ($status) {
    'created' => 'Pet saved successfully.',
    'updated' => 'Pet updated successfully.',
    'deleted' => 'Pet removed.',
    'error' => 'Something went wrong. Try again.',
    default => null,
};

$pets = [];

try {
    $db = get_db();
    $result = $db->query('SELECT * FROM pets ORDER BY created_at DESC');
    if ($result) {
        $pets = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Throwable $exception) {
    $alert = 'Unable to load pets. Please check your database.';
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pet Roster | Pet Admin</title>
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
          <a class="nav-btn active" href="pets.php">Pet roster</a>
          <a class="nav-btn" href="appointments.php">Appointments</a>
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
            <p class="eyebrow">Roster overview</p>
            <h1>All pets on file</h1>
            <p class="lead">
              Each card mirrors what you captured on the dashboard. Use it as a quick reference
              before appointments or follow-up calls.
            </p>
            <div class="header-actions">
              <a class="ghost-btn" href="index.php">Back to dashboard</a>
              <button class="primary-btn" type="button">Export roster</button>
            </div>
          </div>
          <div class="hero-art">
            <div class="hero-glow"></div>
            <img
              src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=800&q=80"
              alt="Curious kitten looking at camera"
              loading="lazy"
            />
          </div>
        </header>

        <?php if ($alert): ?>
          <div class="alert alert-info">
            <?= h($alert) ?>
          </div>
        <?php endif; ?>

        <?php if (!$pets): ?>
          <p class="empty">No pets in the system yet. Add one from the dashboard.</p>
        <?php else: ?>
          <div class="cards-grid">
            <?php foreach ($pets as $pet): ?>
              <article class="pet-card">
                <div class="pet-card-header">
                  <div class="avatar"></div>
                  <div>
                    <strong><?= h($pet['name']) ?></strong>
                    <p class="pet-meta">
                      <?= h($pet['species']) ?>
                      <?php if ($pet['breed']): ?>
                        · <?= h($pet['breed']) ?>
                      <?php endif; ?>
                    </p>
                  </div>
                  <?php if (!empty($pet['age'])): ?>
                    <span class="badge"><?= h(rtrim(rtrim($pet['age'], '0'), '.')) ?> yrs</span>
                  <?php endif; ?>
                </div>
                <dl>
                  <?php if (!empty($pet['weight'])): ?>
                    <div>
                      <dt>Weight</dt>
                      <dd><?= h($pet['weight']) ?> kg</dd>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($pet['owner_name'])): ?>
                    <div>
                      <dt>Owner</dt>
                      <dd><?= h($pet['owner_name']) ?></dd>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($pet['owner_contact'])): ?>
                    <div>
                      <dt>Contact</dt>
                      <dd><?= h($pet['owner_contact']) ?></dd>
                    </div>
                  <?php endif; ?>
                </dl>
                <p class="pet-notes">
                  <?= $pet['notes'] ? h($pet['notes']) : 'No notes added.' ?>
                </p>
                <p class="timestamp">
                  Created <?= h(date('M j, Y g:i A', (int) $pet['created_at'])) ?>
                </p>
                <div class="actions">
                  <a class="ghost-btn" href="index.php?edit=<?= h((string) $pet['id']) ?>">Edit</a>
                  <form action="pet_delete.php" method="POST" onsubmit="return confirm('Delete this pet profile?');">
                    <input type="hidden" name="id" value="<?= h((string) $pet['id']) ?>" />
                    <button class="ghost-btn destructive" type="submit">Delete</button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
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
        <p>Keep every tail wagging with real-time roster insights and calm client touchpoints.</p>
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
          <h4>Care providers</h4>
          <ul>
            <li>Dr. Riley Ford</li>
            <li>Groom Spa Team</li>
            <li>On-call triage</li>
          </ul>
        </div>
      </div>
    </footer>
  </body>
</html>

