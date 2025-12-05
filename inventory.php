<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$db = get_db();

$status = $_GET['status'] ?? null;
$alerts = [
    'created' => 'Inventory item added.',
    'updated' => 'Inventory item updated.',
    'deleted' => 'Inventory item deleted.',
    'error' => 'Something went wrong. Please try again.',
];
$alert = $alerts[$status] ?? null;

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
$editItem = null;

if ($editId) {
    $stmt = $db->prepare('SELECT * FROM inventory_items WHERE id = ?');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editItem = $result->fetch_assoc() ?: null;
    $stmt->close();
}

$items = [];
$result = $db->query('SELECT * FROM inventory_items ORDER BY created_at DESC');
if ($result) {
    $items = $result->fetch_all(MYSQLI_ASSOC);
}

$stats = [
    'total' => count($items),
    'low' => 0,
    'value' => 0.0,
];

foreach ($items as $item) {
    if ((int) $item['quantity'] <= (int) $item['reorder_level']) {
        $stats['low']++;
    }
    if (!empty($item['unit_cost'])) {
        $stats['value'] += (float) $item['unit_cost'] * (int) $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inventory | Pet Admin</title>
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
          <a class="nav-btn" href="appointments.php">Appointments</a>
          <a class="nav-btn active" href="inventory.php">Inventory</a>
        </nav>
        <div class="sidebar-footer">
          <p>Logged in as</p>
          <strong>Dr. Riley Ford</strong>
        </div>
      </aside>
      <main class="content">
        <header class="content-header">
          <div class="hero-copy">
            <p class="eyebrow">Stock control</p>
            <h1>Inventory pulse board</h1>
            <p class="lead">
              Track meds, supplies, and retail items with reorder targets so your team never runs out mid-visit.
            </p>
            <div class="header-actions">
              <a class="ghost-btn" href="index.php">Back to dashboard</a>
            </div>
          </div>
          <div class="hero-art">
            <div class="hero-glow"></div>
            <img
              src="https://images.unsplash.com/photo-1516728778615-2d590ea1855e?auto=format&fit=crop&w=800&q=80"
              alt="Shelves with pet supplies"
              loading="lazy"
            />
          </div>
        </header>

        <?php if ($alert): ?>
          <div class="alert alert-info"><?= h($alert) ?></div>
        <?php endif; ?>

        <section class="stats-grid">
          <article class="stat-card">
            <p class="label">Tracked items</p>
            <p class="value"><?= h((string) $stats['total']) ?></p>
            <p class="delta neutral">SKUs</p>
          </article>
          <article class="stat-card">
            <p class="label">Low or due soon</p>
            <p class="value"><?= h((string) $stats['low']) ?></p>
            <p class="delta">Needs reorder</p>
          </article>
          <article class="stat-card">
            <p class="label">Inventory value</p>
            <p class="value">$<?= h(number_format($stats['value'], 2)) ?></p>
            <p class="delta">Cost basis</p>
          </article>
        </section>

        <section class="panel">
          <div class="panel-head">
            <div>
              <p class="eyebrow"><?= $editItem ? 'Edit inventory item' : 'Add inventory item' ?></p>
              <h2>Inventory manager</h2>
            </div>
            <?php if ($editItem): ?>
              <a class="ghost-btn" href="inventory.php">Cancel edit</a>
            <?php endif; ?>
          </div>
          <form class="form-grid" action="inventory_save.php" method="POST">
            <?php if ($editItem): ?>
              <input type="hidden" name="id" value="<?= h((string) $editItem['id']) ?>" />
            <?php endif; ?>
            <label>
              Item name
              <input
                name="item_name"
                type="text"
                required
                value="<?= h($editItem['item_name'] ?? '') ?>"
                placeholder="Flea & tick tablets"
              />
            </label>
            <label>
              Category
              <input
                name="category"
                type="text"
                value="<?= h($editItem['category'] ?? '') ?>"
                placeholder="Pharmacy"
              />
            </label>
            <label>
              Quantity
              <input
                name="quantity"
                type="number"
                min="0"
                required
                value="<?= h($editItem['quantity'] ?? 0) ?>"
              />
            </label>
            <label>
              Reorder level
              <input
                name="reorder_level"
                type="number"
                min="0"
                required
                value="<?= h($editItem['reorder_level'] ?? 0) ?>"
              />
            </label>
            <label>
              Unit cost ($)
              <input
                name="unit_cost"
                type="number"
                min="0"
                step="0.01"
                value="<?= h($editItem['unit_cost'] ?? '') ?>"
              />
            </label>
            <label>
              Supplier
              <input
                name="supplier"
                type="text"
                value="<?= h($editItem['supplier'] ?? '') ?>"
                placeholder="SunPet Distributors"
              />
            </label>
            <label>
              Status
              <?php $currentStatus = $editItem['status'] ?? 'In stock'; ?>
              <select name="status" required>
                <?php foreach (['In stock', 'Low', 'On order', 'Discontinued'] as $statusOption): ?>
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
                placeholder="Lot numbers, vendor promises, storage reminders..."
              ><?= h($editItem['notes'] ?? '') ?></textarea>
            </label>
            <div class="form-actions">
              <button class="primary-btn" type="submit">
                <?= $editItem ? 'Update item' : 'Save item' ?>
              </button>
              <button class="ghost-btn" type="reset">Reset</button>
            </div>
          </form>
        </section>

        <section class="panel">
          <div class="panel-head">
            <div>
              <p class="eyebrow">Summary</p>
              <h2>Stock overview</h2>
            </div>
          </div>
          <?php if (!$items): ?>
            <p class="empty">No inventory captured yet.</p>
          <?php else: ?>
            <div class="table-wrapper">
              <table>
                <thead>
                  <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Reorder</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Value</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($items as $item): ?>
                    <tr>
                      <td>
                        <strong><?= h($item['item_name']) ?></strong>
                        <p class="pet-notes"><?= $item['notes'] ? h($item['notes']) : '&nbsp;' ?></p>
                      </td>
                      <td><?= h($item['category'] ?: '—') ?></td>
                      <td><?= h((string) $item['quantity']) ?></td>
                      <td><?= h((string) $item['reorder_level']) ?></td>
                      <td><?= h($item['supplier'] ?: '—') ?></td>
                      <td><?= h($item['status']) ?></td>
                      <td>
                        <?php
                        $value = '';
                        if (!empty($item['unit_cost'])) {
                            $value = '$' . number_format((float) $item['unit_cost'] * (int) $item['quantity'], 2);
                        }
                        ?>
                        <?= $value ?: '—' ?>
                      </td>
                      <td class="actions">
                        <a class="ghost-btn" href="inventory.php?edit=<?= h((string) $item['id']) ?>">Edit</a>
                        <form action="inventory_delete.php" method="POST" onsubmit="return confirm('Delete this item?');">
                          <input type="hidden" name="id" value="<?= h((string) $item['id']) ?>" />
                          <button class="ghost-btn destructive" type="submit">Delete</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
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
        <p>Inventory stays glowing so clinical care never skips a beat.</p>
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
          <h4>Vendors</h4>
          <ul>
            <li>SunPet Supply</li>
            <li>Northwind Pharma</li>
            <li>Local artisans</li>
          </ul>
        </div>
      </div>
    </footer>
  </body>
</html>


