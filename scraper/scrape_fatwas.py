import puppeteer from "puppeteer";
import axios from "axios";

const BASE_URL = "https://efatwa.muftiwp.gov.my/";

const getFatwas = async () => {
  const browser = await puppeteer.launch({
    headless: false,
    slowMo: 100,
    defaultViewport: null,
  });

  const page = await browser.newPage();
  await page.goto(BASE_URL, { waitUntil: "domcontentloaded" });

  let pageNum = 1;

  while (true) {
    console.log(`📄 Scraping page ${pageNum}...`);

    await page.waitForSelector("table");

    const fatwas = await page.evaluate(() => {
      const rows = Array.from(document.querySelectorAll("tbody tr"));
      return rows.map(row => {
        const cols = row.querySelectorAll("td");
        const linkForm = row.querySelector("form");
        const actionURL = linkForm ? linkForm.getAttribute("action") : null;

        return {
          title: cols[1]?.innerText.trim() || "No title",
          content: actionURL || "No link available",
          date_issued: cols[2]?.innerText.trim() || "",
          issuing_agency: null,
          category_id: null,
        };
      });
    });

    for (const fatwa of fatwas) {
      try {
        const response = await axios.post("http://localhost:8000/api/fatwas/store", fatwa, {
          headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
          },
        });
        console.log("✅ Stored:", fatwa.title);
      } catch (error) {
        console.error("❌ Failed to store fatwa:", fatwa.title);
        if (error.response) {
          console.error(error.response.data);
        } else {
          console.error(error.message);
        }
      }
    }

    // Check for pagination: find a NEXT button by its text or class
    const nextExists = await page.$('ul.pagination li a[rel="next"]');

    if (nextExists) {
      console.log(`➡️ Going to next page (${pageNum + 1})`);
      await Promise.all([
        nextExists.click(),
        page.waitForNavigation({ waitUntil: "domcontentloaded" }),
      ]);
      pageNum++;
    } else {
      console.log("🚫 No more pages.");
      break;
    }
  }

  await browser.close();
};

getFatwas();
