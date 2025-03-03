<template>
  <div class="container-fluid catalogues">
    <div class="row">
      <section class="col-md col-12">
        <div id="musterdatenkatalog"></div>        
      </section>
    </div>
    <div class="row">
      <section class="col-md col-12">
        <div class="alert alert-primary mt-3 d-flex flex-row"
             :class="{ 'alert-danger': getCataloguesCount <= 0 && !getLoading}">
          <div>
            {{ getLoading ? $t('message.catalogues.loadingMessage'):`${getCataloguesCount}
            ${$t('message.catalogues.countMessage')}`}}
          </div>
          <div class="loading-spinner ml-3" v-if="getLoading"></div>
        </div>
        <div class="alert alert-primary mt-3 d-flex flex-row"
             :class="{ 'alert-danger': getDatasetsCount <= 0 && !getLoading}">
          <div>
            {{ getLoading ? $t('message.datasets.loadingMessage'):`${getDatasetsCount}
            ${$t('message.datasets.countMessage')}`}}
          </div>
          <div class="loading-spinner ml-3" v-if="getLoading"></div>
        </div>
        <selectedFacetsOverview :selected-facets="getFacets"/>
        <data-info-box class="catalogue" v-for="catalogue in getCatalogues" :key="catalogue.idName" ref="catalogueBox"
                       :link-to="`datasets?catalog=${catalogue.idName}&showcataloguedetails=true`"
                       :title="getTranslationFor(catalogue.title, $i18n.locale, has(catalogue, 'country.id') ? [catalogue.country.id].concat(catalogue.languages) : catalogue.languages) || catalogue.id"
                       :description="getTranslationFor(catalogue.description, $i18n.locale, has(catalogue, 'country.id') ? [catalogue.country.id].concat(catalogue.languages) : catalogue.languages)"
                       :body-img="getCountryFlagImg(has(catalogue, 'country.id') ? catalogue.country.id : 'eu')"
                       :footer-tags="[`${has(catalogue, 'count') ? catalogue.count : 0}`]">
        </data-info-box>
        <div class="loading-spinner mx-auto mt-3 mb-3" v-if="getLoading"></div>
      </section>
    </div>
  </div>
</template>

<script>
// Import vuex helpers
import { mapActions, mapGetters } from 'vuex';
// Import custom helpers
import { debounce, has } from 'lodash';
// import jQuery
import $ from 'jquery';
import fileTypes from '../utils/fileTypes';
// Import components used in template
import DatasetFacets from './DatasetFacets';
import DataInfoBox from './DataInfoBox';
import Pagination from './Pagination';
// Import filters
import dateFilters from '../filters/dateFilters';
import SelectedFacetsOverview from './SelectedFacetsOverview';
import { getCountryFlagImg, getTranslationFor } from '../utils/helpers';

export default {
  name: 'catalogues',
  dependencies: ['DatasetService', 'CatalogueService'],
  components: {
    selectedFacetsOverview: SelectedFacetsOverview,
    dataInfoBox: DataInfoBox,
    datasetFacets: DatasetFacets,
    pagination: Pagination,
  },
  props: {
    infiniteScrolling: {
      type: Boolean,
      default: false,
    },
    pagination: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      autocompleteData: {
        suggestions: {},
        show: true,
      },
      datasetCounts: {},
      debouncedOnBottomScroll: debounce(this.onBottomScroll, 500),
      facetFields: [],
      lang: this.locale,
      query: '',
      sortSelected: 'relevance desc, modification_date desc, title.en asc',
      sortSelectedLabel: this.$t('message.sort.relevance'),

      // Enter url of your cities Open-Data-Portal here
      cityOpenDataPrefix: 'https://opendata.stadt-muenster.de/dataset/',
      // You don't need to change below here
      openNrwPrefix: 'https://open.nrw/api/3/action/package_show?id=',
      json_url: 'city_data.json',
      cityData: {},
    };
  },
  head: {
    meta: [
      { property: 'robots', content: 'follow,noindex' },
    ],
  },
  computed: {
    ...mapGetters('datasets', [
      'getDatasets',
      'getDatasetsCount',
      // 'getFacets',
      // 'getLimit',
      // 'getLoading',
      // 'getOffset',
      // 'getPage',
      // 'getPageCount',
      // 'getAvailableFacets',
    ]),
    ...mapGetters('catalogues', [
      'getCatalogues',
      'getCataloguesCount',
      // 'getFacets',
      // 'getLimit',
      // 'getLoading',
      // 'getOffset',
      // 'getPage',
      // 'getPageCount',
      // 'getAvailableFacets',
    ]),
    /**
     * @description Returns the current page.
     * @returns {Number}
     */
    page() {
      return this.$route.query.page;
    },
    /**
     * @description Returns the active facets.
     * @returns {Object}
     */
    facets() {
      const facets = {};
      for (const field of this.facetFields) {
        let urlFacets = this.$route.query[field];
        if (!urlFacets) urlFacets = [];
        else if (!Array.isArray(urlFacets)) urlFacets = [urlFacets];
        facets[field] = urlFacets;
      }
      return facets;
    },
  },
  methods: {
    ...mapGetters('datasets', [
      'getDatasets',
      'getDatasetsCount',
      'getFacets',
      'getLimit',
      'getLoading',
      'getOffset',
      'getPage',
      'getPageCount',
      'getAvailableFacets',
    ]),
    ...mapActions('catalogues', [
      'autocompleteQuery',
      'loadCatalogues',
      'loadAdditionalCatalogues',
      'setQuery',
      'setPage',
      'useService',
      'addFacet',
      'setFacets',
      'setFacetOperator',
      'setFacetGroupOperator',
      'setPageCount',
      'setSort',
      'setLoading',
    ]),
    // The imported Lodash has function. Must be defined in Methods so we can use it in template
    has,
    getTranslationFor,
    getCountryFlagImg,
    autocomplete(query) {
      this.autocompleteQuery(query)
        .then((response) => {
          this.autocompleteData.suggestions = [];
          const suggestions = response.data.result;
          const displayedSuggestions = [];
          for (const ds of suggestions.results) {
            displayedSuggestions.push(ds);
          }
          this.autocompleteData.suggestions = displayedSuggestions;
          this.autocompleteData.show = query.length !== 0;
        })
        .catch(() => {});
    },
    handleSuggestionSelection(suggestion) {
      this.$router.push({ path: this.$route.path.slice(-1) === '/' ? `${this.$route.path}${suggestion.idName}` : `${this.$route.path}/${suggestion.idName}` });
    },
    changePageTo(page) {
      this.$router.replace({ query: Object.assign({}, this.$route.query, { page }) });
      this.scrollTo(0, 0);
    },
    /**
     * @description Handler-function for the scroll event.
     */
    onScroll() {
      const items = this.$el.querySelectorAll('.catalogue');
      const lastItem = items[items.length - 1];
      if (lastItem) {
        const lastItemPos = lastItem.getBoundingClientRect();
        if (lastItemPos.bottom - window.innerHeight <= 0) {
          this.debouncedOnBottomScroll();
        }
      }
    },
    /**
     * @description Handler-function when bottom of the page is reached.
     */
    onBottomScroll() {
      this.$nextTick(() => {
        this.$Progress.start();
        this.setLoading(true);
        this.loadAdditionalCatalogues()
          .then(() => {
            this.$Progress.finish();
            this.setLoading(false);
          })
          .catch(() => {
            this.$Progress.fail();
            this.setLoading(false);
          });
      });
    },

    /**
     * @description The the current scroll-level to a given point.
     * @param x {Number} - The x-position to scroll to
     * @param y {Number} - The y-position to scroll to
     */
    scrollTo(x, y) {
      window.scrollTo(x, y);
    },
    /**
     * @description Removes the duplicates of the given array
     * @param array {Array} - The array to remove duplicates from
     * @returns {Array}
     */
    removeDuplicatesOf(array) {
      // Array --> Set --> Array to remove duplicates.
      return [...new Set(array)];
    },
    /**
     * @description Determines the current page.
     */
    initPage() {
      const page = parseInt(this.$route.query.page, 10);
      if (page > 0) this.setPage(page);
      else this.setPage(1);
    },
    /**
     * @description Initialize the query String by checking the route parameters
     */
    initQuery() {
      let query = this.$route.query.query;
      if (!query) {
        query = '';
        this.setQuery('');
      } else {
        this.query = query;
        this.setQuery(query);
      }
    },
    /**
     * @descritption Initialize the active facets by checking the route parameters
     */
    initFacets() {
      const fields = ['country', 'catalog', 'categories', 'keywords', 'format', 'licence'];
      for (const field of fields) {
        this.facetFields.push(field);
        if (!Object.prototype.hasOwnProperty.call(this.$route.query, [field])) {
          this.$router.replace({
            query: Object.assign({}, this.$route.query, { [field]: [] }),
          });
        } else {
          for (const facet of this.$route.query[field]) {
            this.addFacet({ field, facet });
          }
        }
      }
    },
    initFacetOperator() {
      const op = this.$route.query.facetOperator;
      if (op === 'AND' || op === 'OR') this.setFacetOperator(op);
    },
    initFacetGroupOperator() {
      // const op = this.$route.query.facetGroupOperator;
      // The facetGroupOperator should be the same as the facetOperator
      const op = this.$route.query.facetOperator;
      if (op === 'AND' || op === 'OR') this.setFacetGroupOperator(op);
    },
    initSort() {
      let sort = this.$route.query.sort;
      if (sort) {
        sort = sort.split(',')[0].toLowerCase();
        if (sort === 'relevance+desc') this.sortSelectedLabel = this.$t('message.sort.relevance');
        if (sort.includes('title') && sort.includes('asc')) this.sortSelectedLabel = this.$t('message.sort.nameAZ');
        if (sort.includes('title') && sort.includes('desc')) this.sortSelectedLabel = this.$t('message.sort.nameZA');
        if (sort === 'modification_date+desc') this.sortSelectedLabel = this.$t('message.sort.lastUpdated');
        if (sort === 'release_date+desc') this.sortSelectedLabel = this.$t('message.sort.lastCreated');
        this.sortSelected = sort;
      }
    },
    getFileTypeColor(format) {
      return fileTypes.getFileTypeColor(format);
    },
    filterDateFormatUS(date) {
      return dateFilters.formatUS(date);
    },
    filterDateFormatEU(date) {
      return dateFilters.formatEU(date);
    },
    filterDateFromNow(date) {
      return dateFilters.fromNow(date);
    },
    setSortMethod(method, order, label) {
      this.sortSelectedLabel = label;
      this.sortSelected = `${method}+${order}`;
    },
    changeQuery(query) {
      // this.autocomplete(query);
      this.$router.replace({ query: Object.assign({}, this.$route.query, { query }) });
      this.setQuery(query);
    },
    showMusterdatenkatalog() {
      $.getJSON('musterdatenkatalog.json', function (musterkatalog) {
        // Aggregate data into a better structure to print the HTML table based on "Thema"
        const table = {};
        Object.entries(musterkatalog).forEach((value) => {
          const topic = value.top;
          const subtopic = value.sub;
          const city = value.org;
          const dataset = value.name;
          const dataId = ''; // valueid
          if (!table[topic]) {
            table[topic] = {};
          }
          if (!table[topic][subtopic]) {
            table[topic][subtopic] = {};
          }
          table[topic][subtopic][city] = [dataId, dataset];
        });

        const $table = $('<table id="m_table" class="prettyTable"/>');
        $table.append('<tr class="head"><th>Musterkat.-Thema</th><th>Musterkat.-Bereich</th><th>Städte mit Datensätzen</th><th id="m_filter">Datenquelle(n) in Münster</th></tr>');
        Object.entries(table).forEach(([topic, subtopics]) => {
          const colspan = Object.keys(subtopics).length + 1;
          const $body = $('<tbody>');
          $body.append(`<tr class="theme"><td rowspan="${colspan}">${topic}</td><td class="no_padding" colspan="3"></td></tr>`);
          // let counter = 0;

          Object.entries(subtopics).forEach(([subtopic, cities]) => {
            // Display the other cities that have this dataset
            const numberOfCities = Object.keys(cities).length;
            let citylistHtml = '';
            Object.entries(cities).forEach(([city, content]) => {
              citylistHtml += `<b>${city}</b>:
                <a target="_blank" href="${this.openNrwPrefix}${content[0]}">${content[1]}</a><br />`;
            });

            // Display the departments of Münster (if we have a matching dataset)
            const taxonomy = `${topic} - ${subtopic}`;
            const activeDepartments = [];
            let ourCityHtml = '';
            if (this.cityData[taxonomy]) {
              let ourCityDatasets = '';
              Object.entries(this.cityData[taxonomy]).forEach(([department, datasets]) => {
                activeDepartments.push(department);
                datasets.forEach(function (dataset) {
                  ourCityDatasets += `<br /><b>&gt;</b> <a target="_blank" href="${this.cityOpenDataPrefix}${dataset}">${dataset}</a>`;
                });
              });
              ourCityHtml += activeDepartments.join(', ') + ourCityDatasets;
            }

            // Now render the table row
            const tdClass = (ourCityHtml ? ' class="gotIt"' : '');
            // $table.append( ((counter++==0)?'':'<tr class="ok">')
            const stars = numberOfCities > 1 ? (`<i>${'✪'.repeat(numberOfCities)}</i><br />`) : '';
            $body.append(`<tr ${tdClass}>
              <td>${subtopic}</td>
              <td><div class="toggle">
                ${stars}
                ${citylistHtml}</div></td>
              <td class="amt"><div class="toggle">${ourCityHtml}</div></td></tr>`);
          });
          $table.append($body);
        });
        $('#musterdatenkatalog').append($table);

        // Onclick table cell expander toggle
        $('.prettyTable div.toggle').click(function () {
          $(this).toggleClass('open');
        });

        $.getJSON('city_aemter.json', (aemter) => {
          const $select = $('<select id="filter" />');
          const emptyOption = ' - Alle Ämter anzeigen - ';
          $select.append(`<option value="">${emptyOption}</option>`);
          Object.entries(aemter).forEach((value) => {
            $select.append(`<option value="${value.amt}">${value.dez} - ${value.id} ${value.amt}</option>`);
          });
          $('#m_filter').append('<br />').append($select);
          $('#filter').on('change', function () {
            const amt = $(this).val();
            $('#m_table tbody').hide();
            $('#m_table tr td.amt').removeClass('highlight');
            $('#m_table tr td.amt').each(function () {
              if ((!amt) || ($(this).text().includes(amt))) {
                $(this).parent().parent().show(); // show surrounding tbody
                if (amt) {
                  $(this).addClass('highlight');
                }
              }
            });
          });
        });
      });
    },
  },
  watch: {
    /**
     * @descritpion Watcher for active facets
     */
    facets: {
      handler(facets) {
        this.setFacets(facets);
      },
      deep: true,
    },
    page(pageStr) {
      const page = parseInt(pageStr, 10);
      if (page > 0) this.setPage(page);
      else this.setPage(1);
    },
    sortSelected: {
      handler(sort) {
        this.$router.replace({ query: Object.assign({}, this.$route.query, { sort }) });
        this.setSort(sort);
      },
      deep: true,
    },
  },
  created() {
    this.useService(this.DatasetService);
    // this.useService(this.CatalogueService);
    this.initPage();
    this.initQuery();
    this.initSort();
    this.initFacetOperator();
    this.initFacetGroupOperator();
    this.initFacets();
    this.$nextTick(() => {
      console.log('xxx');
      this.$Progress.start();
      /* this.loadCatalogues({})
        .then(() => {
          this.setPageCount(Math.ceil(this.getCataloguesCount / this.getLimit));
          this.$Progress.finish();
        })
        .catch(() => this.$Progress.fail()); */
      this.loadDatasets({})
        .then(() => {
          console.log('foooo');
          this.setPageCount(Math.ceil(this.getDatasetsCount / this.getLimit));
          this.$Progress.finish();
          $('[data-toggle="tooltip"]').tooltip({
            container: 'body',
          });
        })
        .catch(() => {
          this.$Progress.fail();
        });
    });
    if (this.infiniteScrolling) window.addEventListener('scroll', this.onScroll);
  },
  mounted() {
  },
  beforeDestroy() {
    if (this.infiniteScrolling) window.removeEventListener('scroll', this.onScroll);
  },
};
</script>

<style lang="scss" scoped>
  @import '../styles/bootstrap_theme';
  @import '../styles/utils/css-animations';

  .suggestion-input-group {
    position: relative;
  }
  .suggestion-input {
    position: absolute;
    top: 0;
  }
  .suggestion-list-group {
    position: relative;
    width: 100%;
  }
  .suggestion-list {
    width: 100%;
    position: absolute;
    top: 0;
    z-index: 100;
  }

    .chart {
        height: 440px;
    }
    .prettyTable {
        font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }
    .prettyTable td, .prettyTable th {
        border: 1px solid #ddd;
        padding: 4px 8px;
    }
    .prettyTable tr.theme {
        background-color: #BEC8BE;
    }
    .prettyTable tr.gotIt td {
        background-color:#BDDDBF;
    }
    .prettyTable tr:nth-child(even){background-color: #f2f2f2;}

    .prettyTable tr:hover {background-color: #ddd;}

    .prettyTable th {
        padding: 8px;
        text-align: left;
        background-color: #4CAF50;
        color: white;
    }

    .prettyTable div.toggle {
        max-height:20px;
        overflow:hidden;
        cursor:pointer;
    }
    .prettyTable div.toggle.open {
        max-height:none;
        overflow:visible;
    }
    .prettyTable div.toggle i, .prettyTable div.toggle b {
        font-size: 13px;
        font-style: normal;
        background-color: #36c;
        border-radius: 5px;
        color: white;
        padding: 0 3px;
    }
    .prettyTable div.toggle b {
        background-color: #999;
    }
    .prettyTable div.toggle a {
        color:grey;
    }
    .anleitung {
        padding: 5px 10px;
        background-color: aliceblue;
        margin-bottom: 4px;
        border-radius: 10px;
        font-size: 0.9em;
    }
    .prettyTable tbody tr td.highlight {background-color:yellow;}
    .prettyTable tr td.no_padding {padding:0}
</style>
