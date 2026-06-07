import { defineConfig, devices } from '@playwright/test';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
process.env.PLAYWRIGHT_BROWSERS_PATH = path.join(__dirname, '.playwright-browsers');

export default defineConfig({
    testDir: './tests/Browser',
    timeout: 45_000,
    expect: {
        timeout: 10_000,
    },
    fullyParallel: false,
    workers: 1,
    retries: 0,
    globalSetup: './tests/Browser/global-setup.js',
    use: {
        baseURL: 'http://127.0.0.1:8001',
        trace: 'retain-on-failure',
    },
    webServer: {
        command: 'php artisan serve --port=8001 --no-reload',
        url: 'http://127.0.0.1:8001',
        reuseExistingServer: !process.env.CI,
        timeout: 120_000,
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});
