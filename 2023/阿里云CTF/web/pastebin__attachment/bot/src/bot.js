const puppeteer = require("puppeteer");
const fs = require("fs").promises;

const HOST = process.env.HOST || "localhost";
const PORT = process.env.PORT || "4000";
const CHALLURL = `http://${HOST}:${PORT}`;
const ADMIN_USERNAME = process.env.ADMIN_USERNAME || "admin";
const ADMIN_PASS = process.env.ADMIN_PASS || "admin";
const COOKIE_PATH = "/app/cookies.json";

const visit = (url) => {
    let browser, page;
    return new Promise(async (resolve, reject) => {
        try {
            browser = await puppeteer.launch({
                headless: true,
                args: [
                    "--no-sandbox",
                    "--disable-setuid-sandbox",
                    "--ignore-certificate-errors",
                ],
                timeout: 1000 * 60 * 5,
            });

            page = await browser.newPage();

            let cookieString = await fs.readFile(COOKIE_PATH, "utf8");
            let cookies = JSON.parse(cookieString);
            let needLogin = false;

            for (element of cookies) {
                await page.setCookie(element);
                if (element["name"] == "login" && element["value"] == "0")
                    needLogin = true;
            }

            if (needLogin) {
                await page.goto(CHALLURL + "/user/login", {
                    timeout: 0,
                });

                await page.type("#username", ADMIN_USERNAME, {
                    delay: 100,
                });
                await page.type("#password", ADMIN_PASS, {
                    delay: 100,
                });
                await Promise.all([
                    page.click("#submit"),
                    page.waitForNavigation({ waitUntil: "networkidle2" }),
                ]);
                cookies = await page.cookies();
                await fs.writeFile(
                    COOKIE_PATH,
                    JSON.stringify(cookies, null, 2)
                );
            }

            console.log("start visit: " + url);
            page.setDefaultTimeout(1000 * 60 * 5);
            await page.goto(url, { waitUntil: "networkidle0", timeout: 0 });
            await page.waitForNetworkIdle();

            await new Promise((resolve) => setTimeout(resolve, 3e3));

            var u = new URL(url);
            if (u.host == HOST + ":" + PORT) {
                cookies = await page.cookies();
                console.log(cookies);
                await fs.writeFile(
                    COOKIE_PATH,
                    JSON.stringify(cookies, null, 2)
                );
            }

            console.log(`[-] done: ${url}`);

            await page.close();
            await browser.close();
            page = null;
            browser = null;
        } catch (err) {
            console.log(err);
        } finally {
            if (page) await page.close();
            if (browser) await browser.close();
            resolve();
        }
    });
};

module.exports = {
    visit,
};