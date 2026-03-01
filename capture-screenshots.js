// capture-screenshots.js
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

// -------- CONFIGURE THESE --------

// Role credentials (all use admin login form / login-handler). Logout between roles so each dashboard is captured with correct session.
const ROLE_CREDENTIALS = {
  admin:    { loginUrl: 'http://localhost/restaurant/frontend/admin/login.php', username: 'admin',    password: 'admin123' },
  manager:  { loginUrl: 'http://localhost/restaurant/frontend/admin/login.php', username: 'manager',  password: 'password' },
  waiter:   { loginUrl: 'http://localhost/restaurant/frontend/admin/login.php', username: 'waiter',   password: 'password' },
  chef:     { loginUrl: 'http://localhost/restaurant/frontend/admin/login.php', username: 'chef',     password: 'password' },
};
const LOGOUT_URL = 'http://localhost/restaurant/frontend/admin/logout.php';

// Pages to capture. auth: false = capture before login; auth: 'admin'|'manager'|'waiter'|'chef' = capture after logging in as that role.
const PAGES = [
  // Public/customer-facing (capture before login)
  { url: 'http://localhost/restaurant/frontend/index.php', name: 'home', auth: false },
  { url: 'http://localhost/restaurant/frontend/cart.php', name: 'cart', auth: false },
  { url: 'http://localhost/restaurant/frontend/order-confirmation.php', name: 'order-confirmation', auth: false },
  { url: 'http://localhost/restaurant/frontend/feedback.php', name: 'feedback', auth: false },
  { url: 'http://localhost/restaurant/frontend/reviews.php', name: 'reviews', auth: false },

  // Login pages (capture before login so form is visible)
  { url: 'http://localhost/restaurant/frontend/admin/login.php', name: 'admin-login', auth: false },
  { url: 'http://localhost/restaurant/frontend/kitchen/login.php', name: 'kitchen-login', auth: false },
  { url: 'http://localhost/restaurant/frontend/waiter/login.php', name: 'waiter-login', auth: false },

  // Admin-only pages (capture after login as admin)
  { url: 'http://localhost/restaurant/frontend/admin/dashboard.php', name: 'admin-dashboard', auth: 'admin' },
  { url: 'http://localhost/restaurant/frontend/admin/menu-management.php', name: 'admin-menu-management', auth: 'admin' },
  { url: 'http://localhost/restaurant/frontend/admin/order-history.php', name: 'admin-order-history', auth: 'admin' },
  { url: 'http://localhost/restaurant/frontend/admin/category-management.php', name: 'admin-category-management', auth: 'admin' },
  { url: 'http://localhost/restaurant/frontend/admin/analytics.php', name: 'admin-analytics', auth: 'admin' },
  { url: 'http://localhost/restaurant/frontend/admin/store-settings.php', name: 'admin-store-settings', auth: 'admin' },
  { url: 'http://localhost/restaurant/frontend/admin/feedback-dashboard.php', name: 'admin-feedback-dashboard', auth: 'admin' },
  { url: 'http://localhost/restaurant/frontend/admin/reports.php', name: 'admin-reports', auth: 'admin' },
  { url: 'http://localhost/restaurant/frontend/admin/kitchen-dashboard.php', name: 'admin-kitchen-dashboard', auth: 'admin' },
  { url: 'http://localhost/restaurant/frontend/admin/logout.php', name: 'admin-logout', auth: 'admin' },
  { url: 'http://localhost/restaurant/frontend/super-admin/restaurant-management.php', name: 'super-admin-restaurant-management', auth: 'admin' },

  // Manager, waiter, chef: each captured after login as that role (logout before switching)
  { url: 'http://localhost/restaurant/frontend/manager/dashboard.php', name: 'manager-dashboard', auth: 'manager' },
  { url: 'http://localhost/restaurant/frontend/waiter/dashboard.php', name: 'waiter-dashboard', auth: 'waiter' },
  { url: 'http://localhost/restaurant/frontend/kitchen/dashboard.php', name: 'kitchen-dashboard', auth: 'chef' },
  { url: 'http://localhost/restaurant/frontend/chef/dashboard.php', name: 'chef-dashboard', auth: 'chef' },
];

// No sections for now (keep empty)
const PAGE_SECTIONS = {};

// Default viewport size for "screen" screenshots
const VIEWPORT = { width: 1920, height: 1080 };

// Base output folder (no timestamp)
const BASE_OUTPUT_DIR = path.join(process.cwd(), 'screenshots');
const VIEWPORT_DIR = path.join(BASE_OUTPUT_DIR, 'viewport');
const FULL_DIR = path.join(BASE_OUTPUT_DIR, 'full');

// --------- HELPER FUNCTIONS ---------

function slugify(name) {
  return name
    .toString()
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

async function ensureDir(dirPath) {
  await fs.promises.mkdir(dirPath, { recursive: true });
}

// Log in as a specific role (admin, manager, waiter, chef). Uses shared admin login form.
async function loginAsRole(page, role) {
  const creds = ROLE_CREDENTIALS[role];
  if (!creds) throw new Error(`Unknown role: ${role}`);
  console.log(`\nLogging in as ${role} at:`, creds.loginUrl);

  await page.goto(creds.loginUrl, {
    waitUntil: 'networkidle2',
    timeout: 60000,
  });

  await page.waitForSelector('#username', { timeout: 10000 });
  await page.type('#username', creds.username, { delay: 50 });
  await page.type('#password', creds.password, { delay: 50 });

  await Promise.all([
    page.click('button[type="submit"]'),
    page
      .waitForNavigation({
        waitUntil: 'networkidle2',
        timeout: 60000,
      })
      .catch(() => {
        console.warn(`${role} login navigation timeout; continuing anyway.`);
      }),
  ]);

  console.log(`${role} login finished. Current URL:`, page.url());
}

// Log out so the next role can log in with a clean session.
async function logout(page) {
  console.log('Logging out:', LOGOUT_URL);
  await page.goto(LOGOUT_URL, {
    waitUntil: 'networkidle2',
    timeout: 15000,
  });
}

// --------- MAIN SCRIPT ---------

(async () => {
  // Make sure base folder exists
  await ensureDir(BASE_OUTPUT_DIR);
  await ensureDir(VIEWPORT_DIR);
  await ensureDir(FULL_DIR);
  console.log('Base screenshots folder:', BASE_OUTPUT_DIR);

  const browser = await puppeteer.launch({
    headless: 'new', // use true if older Puppeteer
    defaultViewport: VIEWPORT,
  });

  const page = await browser.newPage();

  async function capturePage(pageConfig) {
    const { url, name } = pageConfig;
    const pageSlug = slugify(name);
    console.log(`\nOpening page: ${url} (${pageSlug})`);
    await page.goto(url, {
      waitUntil: 'networkidle2',
      timeout: 60000,
    });
    const viewportPath = path.join(VIEWPORT_DIR, `${pageSlug}.png`);
    await page.screenshot({ path: viewportPath, fullPage: false });
    console.log('Viewport screenshot:', viewportPath);
    const fullPagePath = path.join(FULL_DIR, `${pageSlug}.png`);
    await page.screenshot({ path: fullPagePath, fullPage: true });
    console.log('Full-page screenshot:', fullPagePath);
    const sections = PAGE_SECTIONS[pageSlug] || PAGE_SECTIONS[name] || [];
    for (const section of sections) {
      const sectionSlug = slugify(section.name);
      const sectionPath = path.join(BASE_OUTPUT_DIR, 'sections', `${pageSlug}-${sectionSlug}.png`);
      await ensureDir(path.dirname(sectionPath));
      const element = await page.$(section.selector);
      if (!element) {
        console.warn(`  Section not found: ${section.name} (${section.selector})`);
        continue;
      }
      await element.screenshot({ path: sectionPath });
      console.log('Section screenshot:', sectionPath);
    }
  }

  // Pass 1: Capture public and login pages (no auth) so login forms are visible
  const noAuthPages = PAGES.filter((p) => p.auth === false);
  console.log('\n--- Pass 1: Public & login pages (no auth) ---');
  for (const pageConfig of noAuthPages) {
    await capturePage(pageConfig);
  }

  // Pass 2: For each role, login → capture that role's pages → logout (so manager/waiter/chef get correct session)
  const authPagesByRole = {};
  for (const pageConfig of PAGES) {
    if (pageConfig.auth && pageConfig.auth !== false) {
      const role = pageConfig.auth;
      if (!authPagesByRole[role]) authPagesByRole[role] = [];
      authPagesByRole[role].push(pageConfig);
    }
  }

  for (const [role, rolePages] of Object.entries(authPagesByRole)) {
    console.log(`\n--- Pass 2: Login as ${role}, capture ${rolePages.length} page(s) ---`);
    await loginAsRole(page, role);
    for (const pageConfig of rolePages) {
      await capturePage(pageConfig);
    }
    await logout(page);
  }

  await browser.close();
  console.log('\nAll screenshots captured.');
})().catch((err) => {
  console.error('Error during screenshot capture:', err);
  process.exit(1);
});