<!-- @license Copyright 2019 Fraunhofer FOKUS
              Modifications copyright 2021 Thomas Tursics
              SPDX-License-Identifier: Apache-2.0
-->

<!-- A component to wrap all components that should be present in the HTML-footer -->

<template>
  <footer>
    <nav class="navbar-light bg-light border-top pt-3 pb-3">
      <div class="content-wrapper navbar navbar-expand-sm">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <app-link class="footer-link" to="https://portal.opendata.guru/">
              <img src="@/assets/img/dlh-head.png" height="42" alt="3 Länder Hack Portal" />
            </app-link>
          </li>
          <li class="nav-item">
            <app-link class="footer-link" :to="{ path: '/imprint'}">Impressum</app-link>
          </li>
          <li class="nav-item">
            <app-link class="footer-link" :to="{ path: '/privacypolicy'}">Datenschutz</app-link>
          </li>
        </ul>
      </div>
    </nav>
  </footer>
</template>

<script>
/* eslint-disable-next-line */
import { glueConfig as GLUE_CONFIG } from '../../config/user-configs';
import AppLink from './AppLink';

export default {
  name: 'bottom',
  components: {
    AppLink,
  },
  data() {
    return {
      footerLogos: GLUE_CONFIG.images.footerLogos,
    };
  },
};
</script>

<style lang="scss" scoped>
.nav-item {
  margin: auto;
}

.footer-link {
  padding: 8px;
}
</style>
