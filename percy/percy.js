const puppeteer = require('puppeteer');
const percySnapshot = require('@percy/puppeteer');
const scrollToBottom = require('scroll-to-bottomjs');

(async () => {
  const browser = await puppeteer.launch({ headless: true, args: [
    '--no-sandbox',
    '--disable-setuid-sandbox',
  ]});

  const scrollOptions = {
    frequency: 100,
    timing: 200, // milliseconds
  };

  // Test Page
  const testPage = await browser.newPage();
  await testPage.goto('https://dev-54ta5gq-4nvswumupeimi.us-4.platformsh.site/story/colby-announces-recent-faculty-promotions/');
  await new Promise(function (resolve) {
    setTimeout(async function  () {
      await testPage.evaluate(scrollToBottom, scrollOptions);

      await percySnapshot(testPage, 'Snapshot of test page', {
        percyCSS: `.relatedSection { display:none; } .highlightsSection { display: none; } .read-time { display: none; }`,
      });
      resolve();
    }, 3000);
  });

  // Test Page 2
  const testPage2 = await browser.newPage();
  await testPage2.goto(
    'https://dev-54ta5gq-4nvswumupeimi.us-4.platformsh.site/story/scholarship-stories-about-colby-faculty-in-2023/'
  );
  await new Promise(function (resolve) {
    setTimeout(async function  () {
      await testPage2.evaluate(scrollToBottom, scrollOptions);

      await percySnapshot(testPage2, 'Snapshot of test page 2', {
        percyCSS: `.relatedSection { display:none; } .highlightsSection { display: none; } .read-time { display: none; }`,
      });
      resolve();
    }, 3000);
  });

  // Test Page 3
  const testPage3 = await browser.newPage();
  await testPage3.goto(
    'https://dev-54ta5gq-4nvswumupeimi.us-4.platformsh.site/story/an-adrenaline-junkie-with-a-passion-for-filmmaking/'
  );
  await new Promise(function (resolve) {
    setTimeout(async function  () {
      await testPage3.evaluate(scrollToBottom, scrollOptions);

      await percySnapshot(testPage3, 'Snapshot of test page 3', {
        percyCSS: `.relatedSection { display:none; } .highlightsSection { display: none; } .read-time { display: none; }`,
      });
      resolve();
    }, 3000);
  });
  
  // Test Page 4
  const testPage4 = await browser.newPage();
  await testPage4.goto(
    'https://dev-54ta5gq-4nvswumupeimi.us-4.platformsh.site/story/buoyed-by-hope-and-light-class-of-2024-departs-mayflower-hill/'
  );
  await new Promise(function (resolve) {
    setTimeout(async function  () {
      await testPage4.evaluate(scrollToBottom, scrollOptions);

      await percySnapshot(testPage4, 'Snapshot of test page 4', {
        percyCSS: `.relatedSection { display:none; } .highlightsSection { display: none; } .read-time { display: none; } .wp-block-embed-youtube { .display: none; }`,
      });
      resolve();
    }, 3000);
  });

  // Main Menu
  const homePage = await browser.newPage();
  await homePage.goto('https://dev-54ta5gq-4nvswumupeimi.us-4.platformsh.site');

  await homePage.waitForSelector('.open-menu');
  await homePage.click('.open-menu');

  const mainMenuSelector = '#main-menu';
  await homePage.waitForSelector(mainMenuSelector);

  await percySnapshot(homePage, 'Snapshot of main menu', {
    scope: mainMenuSelector,
  });

  // Contact Page
  const contactPage = await browser.newPage();

  await contactPage.goto('https://dev-54ta5gq-4nvswumupeimi.us-4.platformsh.site/contact/');

  await percySnapshot(contactPage, 'Snapshot of contact page');

  const resourcesPage = await browser.newPage();

  await resourcesPage.goto('https://dev-54ta5gq-4nvswumupeimi.us-4.platformsh.site/resources-for-the-media/');

  await percySnapshot(resourcesPage, 'Snapshot of resources page');

  // Newsletter Page
  const newsletterPage = await browser.newPage();

  await newsletterPage.goto('https://dev-54ta5gq-4nvswumupeimi.us-4.platformsh.site/newsletter/');

  await percySnapshot(newsletterPage, 'Snapshot of newsletter page');

  await browser.close();
})();
