// capture-screenshots.js
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

// -------- CONFIGURE THESE --------

// All main site and admin pages to capture
const PAGES = [
  // Public/customer-facing pages
  {
    url: 'http://localhost/restaurant/frontend/index.php',
    name: 'home',
  },
  {
    url: 'http://localhost/restaurant/frontend/cart.php',
    name: 'cart',
  },
  {
    url: 'http://localhost/restaurant/frontend/order-confirmation.php',
    name: 'order-confirmation',
  },
  {
    url: 'http://localhost/restaurant/frontend/feedback.php',
    name: 'feedback',
  },
  {
    url: 'http://localhost/restaurant/frontend/reviews.php',
    name: 'reviews',
  },

  // Admin login + dashboards
  {
    url: 'http://localhost/restaurant/frontend/admin/login.php',
    name: 'admin-login',
  },
  {
    url: 'http://localhost/restaurant/frontend/admin/dashboard.php',
    name: 'admin-dashboard',
  },
  {
    url: 'http://localhost/restaurant/frontend/admin/menu-management.php',
    name: 'admin-menu-management',
  },
  {
    url: 'http://localhost/restaurant/frontend/admin/order-history.php',
    name: 'admin-order-history',
  },
  {
    url: 'http://localhost/restaurant/frontend/admin/category-management.php',
    name: 'admin-category-management',
  },
  {
    url: 'http://localhost/restaurant/frontend/admin/analytics.php',
    name: 'admin-analytics',
  },
  {
    url: 'http://localhost/restaurant/frontend/admin/store-settings.php',
    name: 'admin-store-settings',
  },
  {
    url: 'http://localhost/restaurant/frontend/admin/feedback-dashboard.php',
    name: 'admin-feedback-dashboard',
  },
  {
    url: 'http://localhost/restaurant/frontend/admin/reports.php',
    name: 'admin-reports',
  },
  {
    url: 'http://localhost/restaurant/frontend/admin/kitchen-dashboard.php',
    name: 'admin-kitchen-dashboard',
  },
  {
    url: 'http://localhost/restaurant/frontend/admin/logout.php',
    name: 'admin-logout',
  },

  // Other role dashboards & logins
  {
    url: 'http://localhost/restaurant/frontend/kitchen/login.php',
    name: 'kitchen-login',
  },
  {
    url: 'http://localhost/restaurant/frontend/kitchen/dashboard.php',
    name: 'kitchen-dashboard',
  },
  {
    url: 'http://localhost/restaurant/frontend/waiter/login.php',
    name: 'waiter-login',
  },
  {
    url: 'http://localhost/restaurant/frontend/waiter/dashboard.php',
    name: 'waiter-dashboard',
  },
  {
    url: 'http://localhost/restaurant/frontend/chef/dashboard.php',
    name: 'chef-dashboard',
  },
  {
    url: 'http://localhost/restaurant/frontend/manager/dashboard.php',
    name: 'manager-dashboard',
  },
  {
    url: 'http://localhost/restaurant/frontend/super-admin/restaurant-management.php',
    name: 'super-admin-restaurant-management',
  },
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

// Perform a one-time admin login so authenticated pages load correctly
async function loginAsAdmin(page) {
  const LOGIN_URL = 'http://localhost/restaurant/frontend/admin/login.php';
  console.log('\nLogging in as admin at:', LOGIN_URL);

  await page.goto(LOGIN_URL, {
    waitUntil: 'networkidle2',
    timeout: 60000,
  });

  // Fill in known demo credentials from the login page quick access buttons
  await page.waitForSelector('#username', { timeout: 10000 });
  await page.type('#username', 'admin', { delay: 50 });
  await page.type('#password', 'admin123', { delay: 50 });

  // Submit the form and wait for navigation triggered by handleLogin()
  await Promise.all([
    page.click('button[type="submit"]'),
    page
      .waitForNavigation({
        waitUntil: 'networkidle2',
        timeout: 60000,
      })
      .catch(() => {
        console.warn('Admin login navigation timeout; continuing anyway.');
      }),
  ]);

  console.log('Admin login attempt finished. Current URL:', page.url());
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

  // Log in once as admin so all protected pages are accessible
  await loginAsAdmin(page);

  for (const pageConfig of PAGES) {
    const { url, name } = pageConfig;
    const pageSlug = slugify(name);

    console.log(`\nOpening page: ${url} (${pageSlug})`);
    console.log(
      'Saving screenshots to viewport/full folders with image name:',
      pageSlug + '.png'
    );

    await page.goto(url, {
      waitUntil: 'networkidle2',
      timeout: 60000,
    });

    // Viewport screenshot (current window H & W)
    const viewportPath = path.join(VIEWPORT_DIR, `${pageSlug}.png`);
    await page.screenshot({
      path: viewportPath,
      fullPage: false,
    });
    console.log('Viewport screenshot:', viewportPath);

    // Full-page screenshot (complete page)
    const fullPagePath = path.join(FULL_DIR, `${pageSlug}.png`);
    await page.screenshot({
      path: fullPagePath,
      fullPage: true,
    });
    console.log('Full-page screenshot:', fullPagePath);

    // Section screenshots (kept here in case you add later)
    const sections = PAGE_SECTIONS[pageSlug] || PAGE_SECTIONS[name] || [];
    for (const section of sections) {
      const sectionSlug = slugify(section.name);
      const sectionPath = path.join(
        BASE_OUTPUT_DIR,
        'sections',
        `${pageSlug}-${sectionSlug}.png`
      );

      await ensureDir(path.dirname(sectionPath));

      const element = await page.$(section.selector);
      if (!element) {
        console.warn(
          `  Section not found: ${section.name} (${section.selector})`
        );
        continue;
      }

      await element.screenshot({ path: sectionPath });
      console.log('Section screenshot:', sectionPath);
    }
  }

  await browser.close();
  console.log('\nAll screenshots captured.');
})().catch((err) => {
  console.error('Error during screenshot capture:', err);
  process.exit(1);
});