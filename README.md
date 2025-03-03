# 3 Länder Hack, 2021

Todo: write a better description

- Link to portal: http://portal.opendata.guru/
- Link to Metadatenkatalog: http://portal.opendata.guru/musterdatenkatalog/

## Build project for development

    $ npm install
    $ npm run dev

Open a web browser and visit `http://localhost:8084` to see the app.

Hot Module Replacement is supported. The page will update automatically whenever files are changed and saved.

## Build project for production

    $ npm run build

This will optimize files for production and store the bundle in `/dist` folder. Deploy the contents of `/dist` folder on your webserver.

You use a shared hoster (ionos, strato) with Apache webserver? An invisible file is located at `/dist/.htaccess'. Please copy it also to your webservers root.

## Update data

The data is (currently) hand made.

1. Open Spreadsheet https://sbb-my.sharepoint.com/:x:/r/personal/kathrin_ernst_sbb_ch/_layouts/15/Doc.aspx?sourcedoc=%7Bfdc4758e-386f-477c-94dd-6a02260f21cd%7D&action=default&cid=500d42a9-dba3-42ff-852d-cace42b5b71c in browser
2. file -> save a local copy
3. Open in Excel
4. remove all tables, expect the 'Open Data' one
5. save the file as CSV file
6. upload csv to webserver /api/hub/item/
7. open http://api.opendata.guru/hub/item/item.php in browser (this will import the data to database)
8. done

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>

Configurations
=====================

**glue-config.js**
<br>The glue-config.js file is located at `peacock-user-ui/user-config/glue-config.js` by default. It is the main project configuration file. The following table and example file shortly describes the configurable values.

<details>
<summary>Open glue-config.js Example File</summary>

```javascript

// Import Adapters for data requests
import datasetService from '../src/my-adapter-folder/myDatasetService';
import catalogueService from '../src/my-adapter-folder/myCatalogueService';
import distributionService from '../src/my-adapter-folder/myDistributionService';
import datastoreService from '../src/my-adapter-folder/myDatastoreService';
import gazetteerService from '../src/my-adapter-folder/myGazetteerService';

// Exported Config-Object
export default {
  // The Title of the app. Shown in browser tabs.
  title: 'My Awesome Title',
  // The Base Urls used to fetch data from
  api: {
    baseUrl: 'https://www.the-base-url.to/my/data/endpoints/',
    gazetteerBaseUrl: 'https://www.the-base-url.to/my/gazetteer/data/endpoints/', // TODO: find less hacky solution if the app  uses different APIs to fetch data. Maybe baseUrls: [<url1>, <url2>, ...]
  },
  // Images to add to header/footer
  images: {
    // Images/Logos to add to the Header of the webpage
    headerLogos: [
      {
        // Where to get the image from
        src: 'https://link.to/my-header-logo.png',
        // Where does the image link to
        href: 'https://my-external-logo-url.de' // (optional)
        // How to open the page this image links to
        target: '_blank' // (optional)
        // The alternative description of this image
        description: 'My Awesome Header Logo',
        // The css height of this image
        height: '60px',
        // The css width of this image
        width: 'auto',
      },
    ],
    // Images/Logos to add to the Footer of the webpage.
    footerLogos: [
      {
        // Where to get the image from
        src: 'https://link.to/my-footer-logo.png',
        // Where does the image link to
        href: 'https://my-external-logo-url.de' // (optional)
        // How to open the page this image links to
        target: '_blank' // (optional)
        // The alternative description of this image
        description: 'My Awesome Footer Logo',
        // The css height of this image
        height: '80px',
        // The css width of this image
        width: 'auto',
      },
    ],
  },
  // The default language used
  locale: 'en',
  // The fallback language if no translations for another language is available (Atleast this language must be present and complete in your i18n.json file)
  fallbackLocale: 'en',
  // The services fetch data from somewhere.Each Service has to be Imported at the beginning of this file.
  services: {
    catalogueService,
    datasetService,
    distributionService,
    datastoreService,
    gazetteerService,
  },
  
  themes: {
    // Sets the header Theme. Currently Available: 'primary' XOR 'dark' XOR 'light'.
    header: 'dark',
  },
  // Options to configure Vue Router
  routerOptions: {
    // Defines the base URL of the app. -> https://router.vuejs.org/api/#base
    base: '',
    // available values: "hash" | "history" | "abstract" -> https://router.vuejs.org/api/#mode
    mode: 'hash',
  },
  // Navigation related configurations
  navigation: {
    topnav: {
      // The main navigation configurations
      main: {
        home: {
          // If set: The Home navigation item will link to this url.
          // If not set: The Home navigation item will link to the Home.vue component in peacock-user-ui/src/components/
          href: 'https://link-to-external-url.com/home'
          // Defines where to open the target page
          target: '_self',
          // Defines whether this navigation item is shown or not
          show: true,
        },
        data: {
          show: true,
        },
        maps: {
          show: false,
        },
        about: {
          show: false,
        },
        // Contains Navigation items you want to add to the main navigation.
        append: [
          {
            // Defines the url this navigation element leads to
            href: 'https://www.my-privacy-policy-from-somewhere.de',
            // Defines the icon next to the navigation elements text. Currently using material icons: https://material.io/tools/icons/?style=baseline
            icon: 'rowing',
            // Defines where to open the target page
            target: '_self',
            // The title of this navigation element
            title: 'Privacy Policy',
          },
          {
            href: 'https://www.my-general-imprint.de',
            icon: 'info',
            target: '_self',
            title: 'Imprint',
          },
        ],
        // Defines whether to show icons next to each navigation elements title
        icons: true,
      },
      // The sub navigation configurations
      sub: {
        privacyPolicy: {
          // Defines whether this navigation item is shown or not
          show: true,
          // if set: Defines the url this navigation element leads to
          // if not set: This navigation element will link to the userPrivacyPolicy.vue component in peacock-user-ui/src/components/user
          href: 'https://www.some-url.de/privacy-policy',
          // Defines where to open the target page
          target: '_self',
        },
        imprint: {
          // Defines whether this navigation item is shown or not
          show: true,
          // if set: Defines the url this navigation element leads to
          // if not set: This navigation element will link to the userImprint.vue component in peacock-user-ui/src/components/user
          href: 'https://www.some-url.de/imprint',
          // Defines where to open the target page
          target: '_self',
        },
      },
    },
  },
};

```

</details><br>

**custom_theme.scss**
<br>The custom-theme.scss file is located at `peacock-user-ui/user-config/custom-theme.scss` by default. It contains Bootstrap 4 scss variables and overrides the default Bootstrap values. It must be used to change any general styling rules like spacing, sizes, colors etc. You can also add your own color variables to use them via Bootstrap classes or add other new variables.

**i18n.json**
<br>The i18n.json file is located at `peacock-user-ui/user-config/i18n/i18n.json` by default. It contains translations for all available languages for the vue-i18n module.

**user-configs.js**
<br>The user-configs.json file is located at `peacock-user-ui/config/user-configs.js` by default. It contains the paths to the glue-config.js and i18n.json. By default that is the paths as described above.

**UserImprint.vue and UserPrivacyPolicy**
<br>The UserImprint.vue and UserPrivacyPolicy.vue files are located at `peacock-user-ui/src/components/user/`. They are more or less empty vue components where you should implement your Imprint/PrivacyPolicy pages in if you do not use external pages for those cases (configurable in glue-config.js).

**index.js**
<br>The index.js file is located at `peacock-user-ui/config/index.js` by default and is generated by the Vue-Webpack-Bundle. It contains several configurations for the development and production build process.

Run it via Docker
====================
- Build the application for production
```
$ docker build -t edp/peacock-user-ui .
$ docker run -i -p 8080:8080 edp/peacock-user-ui
```

## Runtime Configuration

We utilize a Vue plugin `RuntimeConfiguration` to configure a web application using environment variables without rebuilding it.

See [runtime-config-template.js](./src/utils/runtimeconfig/runtime-config-template.js) for all available runtime variables.

### Usage

Normally, you would use `process.env` to consume environment specific configuration. This changes here.
To consume environment variables in a Vue component, use `this.$env` as you would use `process.env`.

#### Example

```
this.$env.MATOMO_URL // Formerly process.env.MATOMO_URL
```

`this.$env` differs from `process.env` in a way that some properties that are set using environment variables are set here. That means that in a development environment, `this.$env` would be equivalent to `process.env`.

### Creating new runtime variables

If you want to make configuration properties dynamic during runtime using environment variables, follow these steps:

1. In [runtime-config-template.js](./src/utils/runtimeconfig/runtime-config-template.js), add your desired configuration variable as a property and enter its enivronment variable name as value. However, there are some restrictions you must follow:
    -  Environment variable names in [runtime-config-template.js](./src/utils/runtimeconfig/runtime-config-template.js) must have the prefix `$VUE_APP_`.
    -  Ensure that the property you want to replace during runtime is consistent in its name *and* structure.
2.  Build and deploy the application.
3.  Set your environment variable *without* the dollar sign `$` at the beginning. For example, if your new entry in [runtime-config-template.js](./src/utils/runtimeconfig/runtime-config-template.js) is `MATOMO_URL: '$VUE_APP_MATOMO_URL'`, make sure you set the environment variable `VUE_APP_MATOMO_URL`.
4.  Your  variable is now ready for consumption and can be changed during runtime by changing their associated environment variable.

#### Example

Let's suppose `process.env` looks like this (depending on how the project is set up):
```
{
  NODE_ENV: 'production',
  ROOT_API: 'https://www.europeandataportal.eu/metrics',
  ROOT_URL: 'https://www.europeandataportal.eu',
  MATOMO: {
      API_URL: 'https://www.ppe-aws.europeandataportal.eu/piwik/',
      ID: 89
  }
}
```

and you want to change `ROOT_API`, `MATOMO.API_URL`, and `MATOMO.ID` during runtime. Let's go through the steps outlined above:

1.  [runtime-config-template.js](./src/utils/runtimeconfig/runtime-config-template.js), would look like this:
```
export default {
  ROOT_API: '$VUE_APP_ROOT_API',
  MATOMO_URL: {
      API_URL: '$VUE_APP_MATOMO_API_URL',
      ID: '$VUE_APP_MATOMO_ID'
  }
}
```
2.  Build and deploy.
3.  Now set the environment variables. These are: `VUE_APP_ROOT_API`, `VUE_APP_MATOMO_API_URL`, and `VUE_APP_MATOMO_ID`.