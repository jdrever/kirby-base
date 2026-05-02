panel.plugin('open-foundations/kirby-base', {
  sections: {
    quicklinks: {
      data: function () {
        return {
          headline: null, // Stores the 'headline' prop from PHP
          links: [],      // Stores the 'links' array from PHP
        }
      },
      created: async function() {
        // The load() method fetches all data returned by the PHP file
        try {
          const response = await this.load(); // Fetches data from PHP endpoint

          this.headline = response.headline;
          this.links = response.links; // Assigns the Array(links)
        } catch (error) {
          console.error("Failed to load quicklinks section:", error);
        }
      },
      template: `
        <section class="k-section k-quicklinks-section">
          <header class="k-section-header">
            <h2 class="k-headline">{{ headline }}</h2>
          </header>

          <k-list v-if="links.length > 0">
            <k-list-item
              v-for="link in links"
              :key="link.url"
              :text="link.text"
              :info="link.info"
              :link="link.url"
              :target="'_self'"
            />
          </k-list>

          <k-empty
            v-else
            icon="link"
          >
            No quick links have been configured yet.
          </k-empty>

        </section>
      `
    },

    formsubmissionsindex: {
      data: function () {
        return {
          headline: 'Form Submissions',
          formTypes: [],
          totalCount: 0,
          exportAllUrl: ''
        }
      },
      created: async function() {
        try {
          const response = await this.load();
          this.headline     = response.headline;
          this.formTypes    = response.formTypes    || [];
          this.totalCount   = response.totalCount   || 0;
          this.exportAllUrl = response.exportAllUrl || '';
        } catch (error) {
          console.error("Failed to load form submissions index section:", error);
        }
      },
      template: `
        <section class="k-section k-formsubmissionsindex-section">
          <header class="k-section-header">
            <h2 class="k-headline">{{ headline }}</h2>
          </header>

          <div v-if="formTypes.length > 0" style="padding: 0.75rem 0 0.5rem;">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;">
              <thead>
                <tr style="border-bottom: 2px solid var(--color-border);">
                  <th style="text-align: left; padding: 0.5rem 0.75rem; font-size: 0.75rem; color: var(--color-text-dimmed); font-weight: 600;">Form type</th>
                  <th style="text-align: right; padding: 0.5rem 0.75rem; font-size: 0.75rem; color: var(--color-text-dimmed); font-weight: 600;">Submissions</th>
                  <th style="text-align: right; padding: 0.5rem 0.75rem; font-size: 0.75rem; color: var(--color-text-dimmed); font-weight: 600;">Export</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="row in formTypes"
                  :key="row.formType"
                  style="border-bottom: 1px solid var(--color-border);"
                >
                  <td style="padding: 0.6rem 0.75rem; font-size: 0.875rem;">{{ row.formType }}</td>
                  <td style="text-align: right; padding: 0.6rem 0.75rem; font-size: 0.875rem; font-weight: 500;">{{ row.count }}</td>
                  <td style="text-align: right; padding: 0.6rem 0.75rem;">
                    <a
                      :href="row.exportUrl"
                      style="font-size: 0.8rem; color: var(--color-blue-500, #2563eb); text-decoration: none; white-space: nowrap;"
                    >&#8595; CSV</a>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr style="border-top: 2px solid var(--color-border);">
                  <td style="padding: 0.6rem 0.75rem; font-size: 0.875rem; font-weight: 600;">Total</td>
                  <td style="text-align: right; padding: 0.6rem 0.75rem; font-size: 0.875rem; font-weight: 600;">{{ totalCount }}</td>
                  <td style="text-align: right; padding: 0.6rem 0.75rem;">
                    <a
                      :href="exportAllUrl"
                      style="font-size: 0.8rem; color: var(--color-blue-500, #2563eb); text-decoration: none; white-space: nowrap;"
                    >&#8595; All CSV</a>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <k-empty
            v-else
            icon="file-text"
          >
            No form submissions recorded yet.
          </k-empty>
        </section>
      `
    },

    formsubmissionexport: {
      data: function () {
        return {
          headline: 'Export Submissions',
          submissionCount: 0,
          exportUrl: ''
        }
      },
      created: async function() {
        try {
          const response = await this.load();
          this.headline = response.headline;
          this.submissionCount = response.submissionCount;
          this.exportUrl = response.exportUrl;
        } catch (error) {
          console.error("Failed to load form submission export section:", error);
        }
      },
      template: `
        <section class="k-section k-formsubmissionexport-section">
          <header class="k-section-header">
            <h2 class="k-headline">{{ headline }}</h2>
          </header>
          <div style="padding: 0.75rem 0 0.5rem;">
            <p style="margin-bottom: 0.75rem; color: var(--color-text-dimmed); font-size: 0.875rem;">
              {{ submissionCount }} submission{{ submissionCount !== 1 ? 's' : '' }} available for export.
            </p>
            <a
              v-if="submissionCount > 0"
              :href="exportUrl"
              style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: var(--color-blue-500, #2563eb); color: #fff; border-radius: var(--rounded); font-size: 0.875rem; font-weight: 500; text-decoration: none;"
            >
              &#8595; Download CSV
            </a>
            <k-empty v-else icon="file-text">
              No submissions to export yet.
            </k-empty>
          </div>
        </section>
      `
    },

    searchindexstats: {
      data: function () {
        return {
          headline: null,
          stats: { total_pages: 0, last_rebuild: null },
          rebuilding: false
        }
      },
      created: async function() {
        try {
          const response = await this.load();
          this.headline = response.headline;
          this.stats = response.stats || this.stats;
        } catch (error) {
          console.error("Failed to load search index stats section:", error);
        }
      },
      methods: {
        rebuild: async function() {
          this.rebuilding = true;
          try {
            await fetch('/search-rebuild');
            const response = await this.load();
            this.stats = response.stats || this.stats;
          } catch (error) {
            console.error("Failed to rebuild search index:", error);
          } finally {
            this.rebuilding = false;
          }
        }
      },
      template: `
        <section class="k-section k-searchindexstats-section">
          <header class="k-section-header">
            <h2 class="k-headline">{{ headline }}</h2>
          </header>

          <div style="padding: 0.75rem 0 0.5rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem; padding: 0.75rem 1rem; background: var(--color-background); border-radius: var(--rounded);">
              <div>
                <strong>Search</strong>
              </div>
              <div style="color: var(--color-text-dimmed); font-size: 0.875rem;">
                {{ stats.total_pages }} pages indexed
              </div>
              <div style="color: var(--color-text-dimmed); font-size: 0.875rem;">
                Last rebuilt: {{ stats.last_rebuild || 'Never' }}
              </div>
              <div style="margin-left: auto;">
                <button
                  @click="rebuild"
                  :disabled="rebuilding"
                  style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.75rem; background: var(--color-black); color: var(--color-white); border: none; cursor: pointer; border-radius: var(--rounded); font-size: 0.8rem;"
                >
                  <span v-if="rebuilding" style="display: inline-block; width: 0.8rem; height: 0.8rem; border: 2px solid var(--color-white); border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span>
                  {{ rebuilding ? 'Rebuilding...' : 'Rebuild' }}
                </button>
              </div>
            </div>
          </div>

          <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        </section>
      `
    },

    contentindexstats: {
      data: function () {
        return {
          headline: null,
          indexes: [],
          rebuildingIndex: null
        }
      },
      created: async function() {
        try {
          const response = await this.load();
          this.headline = response.headline;
          this.indexes = response.indexes || [];
        } catch (error) {
          console.error("Failed to load content index stats section:", error);
        }
      },
      methods: {
        rebuild: async function(indexName) {
          this.rebuildingIndex = indexName;
          try {
            await fetch('/content-index-rebuild?name=' + encodeURIComponent(indexName));
            const response = await this.load();
            this.indexes = response.indexes || [];
          } catch (error) {
            console.error("Failed to rebuild content index:", error);
          } finally {
            this.rebuildingIndex = null;
          }
        }
      },
      template: `
        <section class="k-section k-contentindexstats-section">
          <header class="k-section-header">
            <h2 class="k-headline">{{ headline }}</h2>
          </header>

          <div v-if="indexes.length > 0" style="padding: 0.75rem 0 0.5rem;">
            <div v-for="idx in indexes" :key="idx.name" style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 0.75rem; padding: 0.75rem 1rem; background: var(--color-background); border-radius: var(--rounded);">
              <div>
                <strong style="text-transform: capitalize;">{{ idx.name }}</strong>
              </div>
              <div style="color: var(--color-text-dimmed); font-size: 0.875rem;">
                {{ idx.total_rows }} pages indexed
              </div>
              <div style="color: var(--color-text-dimmed); font-size: 0.875rem;">
                Last rebuilt: {{ idx.last_rebuild || 'Never' }}
              </div>
              <div style="margin-left: auto;">
                <button
                  @click="rebuild(idx.name)"
                  :disabled="rebuildingIndex === idx.name"
                  style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.75rem; background: var(--color-black); color: var(--color-white); border: none; cursor: pointer; border-radius: var(--rounded); font-size: 0.8rem;"
                >
                  <span v-if="rebuildingIndex === idx.name" style="display: inline-block; width: 0.8rem; height: 0.8rem; border: 2px solid var(--color-white); border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span>
                  {{ rebuildingIndex === idx.name ? 'Rebuilding...' : 'Rebuild' }}
                </button>
              </div>
            </div>
          </div>

          <k-empty
            v-else
            icon="database"
          >
            No content indexes registered.
          </k-empty>

        </section>
      `
    },

    searchanalytics: {
      data: function () {
        return {
          headline: null,
          topTerms: [],
          topKeywords: [],
          summary: { totalSearches: 0, uniqueTerms: 0, dateRange: { from: null, to: null } }
        }
      },
      created: async function() {
        try {
          const response = await this.load();
          this.headline = response.headline;
          this.topTerms = response.topTerms || [];
          this.topKeywords = response.topKeywords || [];
          this.summary = response.summary || this.summary;
        } catch (error) {
          console.error("Failed to load search analytics section:", error);
        }
      },
      computed: {
        formattedDateRange: function() {
          if (!this.summary.dateRange.from || !this.summary.dateRange.to) {
            return 'No data';
          }
          const from = this.summary.dateRange.from.split(' ')[0];
          const to = this.summary.dateRange.to.split(' ')[0];
          return from === to ? from : `${from} to ${to}`;
        }
      },
      template: `
        <section class="k-section k-searchanalytics-section">
          <header class="k-section-header">
            <h2 class="k-headline">{{ headline }}</h2>
          </header>

          <div v-if="topTerms.length > 0 || topKeywords.length > 0" class="k-searchanalytics-content">
            <!-- Summary Stats -->
            <div class="k-searchanalytics-summary" style="display: flex; gap: 2rem; margin-bottom: 1.5rem; padding: 1rem; background: var(--color-background); border-radius: var(--rounded);">
              <div>
                <strong style="font-size: 1.5rem;">{{ summary.totalSearches }}</strong>
                <div style="color: var(--color-text-dimmed); font-size: 0.875rem;">Total Searches</div>
              </div>
              <div>
                <strong style="font-size: 1.5rem;">{{ summary.uniqueTerms }}</strong>
                <div style="color: var(--color-text-dimmed); font-size: 0.875rem;">Unique Terms</div>
              </div>
              <div>
                <div style="color: var(--color-text-dimmed); font-size: 0.875rem;">Date Range</div>
                <div>{{ formattedDateRange }}</div>
              </div>
            </div>

            <!-- Tables Container -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
              <!-- Top Search Terms -->
              <div>
                <h3 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--color-text-dimmed);">Top Search Terms</h3>
                <table style="width: 100%; border-collapse: collapse;">
                  <thead>
                    <tr style="border-bottom: 1px solid var(--color-border);">
                      <th style="text-align: left; padding: 0.5rem 0; font-weight: 600; font-size: 0.75rem; color: var(--color-text-dimmed);">Term</th>
                      <th style="text-align: right; padding: 0.5rem 0; font-weight: 600; font-size: 0.75rem; color: var(--color-text-dimmed);">Count</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in topTerms" :key="'term-' + index" style="border-bottom: 1px solid var(--color-border);">
                      <td style="padding: 0.5rem 0; font-size: 0.875rem;">{{ item.term }}</td>
                      <td style="text-align: right; padding: 0.5rem 0; font-size: 0.875rem; font-weight: 500;">{{ item.count }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Top Keywords -->
              <div>
                <h3 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--color-text-dimmed);">Top Keywords</h3>
                <table style="width: 100%; border-collapse: collapse;">
                  <thead>
                    <tr style="border-bottom: 1px solid var(--color-border);">
                      <th style="text-align: left; padding: 0.5rem 0; font-weight: 600; font-size: 0.75rem; color: var(--color-text-dimmed);">Keyword</th>
                      <th style="text-align: right; padding: 0.5rem 0; font-weight: 600; font-size: 0.75rem; color: var(--color-text-dimmed);">Count</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in topKeywords" :key="'keyword-' + index" style="border-bottom: 1px solid var(--color-border);">
                      <td style="padding: 0.5rem 0; font-size: 0.875rem;">{{ item.keyword }}</td>
                      <td style="text-align: right; padding: 0.5rem 0; font-size: 0.875rem; font-weight: 500;">{{ item.count }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <k-empty
            v-else
            icon="search"
          >
            No search data available yet.
          </k-empty>

        </section>
      `
    },

    filteredpages: {
      data: function () {
        return {
          // Config loaded from PHP section
          headline:      '',
          modelId:       '',
          filterDefs:    {},
          columnDefs:    [],
          pageSize:      25,
          searchEnabled: false,
          defaultSort:   'title asc',
          sortOptions:   [],
          template:      '',

          // Toolbar state (persisted in localStorage)
          active:      {},
          search:      '',
          currentSort: 'title asc',
          currentPage: 1,

          // Dropdown options fetched from API
          options: {},

          // Results
          items:      [],
          total:      0,
          totalPages: 0,
          loading:    false,

          _searchTimer: null
        };
      },

      created: async function () {
        try {
          var response = await this.load();
          this.headline      = response.headline      || '';
          this.modelId       = response.modelId       || '';
          this.filterDefs    = response.filters       || {};
          this.columnDefs    = response.columns       || [];
          this.pageSize      = response.pageSize      || 25;
          this.searchEnabled = response.search        || false;
          this.defaultSort   = response.sortBy        || 'title asc';
          this.sortOptions   = response.sortOptions   || [];
          this.template      = response.template      || '';
          this.currentSort   = response.sortBy        || 'title asc';

          // Initialise every filter field to '' so each select shows "Label: All"
          var initialActive = {};
          Object.keys(this.filterDefs).forEach(function (field) { initialActive[field] = ''; });
          this.active = initialActive;

          // Then overlay any persisted selections
          var saved = this.loadSavedState();
          if (saved.active)  Object.assign(this.active, saved.active);
          if (saved.search)  this.search      = saved.search;
          if (saved.sort)    this.currentSort = saved.sort;
          if (saved.page)    this.currentPage = saved.page;

          await Promise.all([this.loadOptions(), this.loadResults()]);
        } catch (error) {
          console.error('Failed to initialise filteredpages section:', error);
        }
      },

      computed: {
        filterFields: function () {
          return Object.keys(this.filterDefs);
        },
        sortField: function () {
          return this.currentSort.split(' ')[0] || 'title';
        },
        sortDir: function () {
          return (this.currentSort.split(' ')[1] || 'asc');
        },
        hasActiveFilters: function () {
          var self = this;
          return Object.keys(self.active).some(function (k) { return self.active[k] !== ''; });
        }
      },

      methods: {
        loadSavedState: function () {
          try {
            return JSON.parse(localStorage.getItem('bsbi-filteredpages-' + this.modelId) || '{}');
          } catch (e) {
            return {};
          }
        },

        saveState: function () {
          try {
            localStorage.setItem('bsbi-filteredpages-' + this.modelId, JSON.stringify({
              active:  this.active,
              search:  this.search,
              sort:    this.currentSort,
              page:    this.currentPage
            }));
          } catch (e) { /* storage unavailable */ }
        },

        loadOptions: async function () {
          try {
            this.options = await this.$api.get('filtered-pages/options', {
              model_id: this.modelId,
              template: this.template,
              filters:  JSON.stringify(this.filterDefs)
            });
          } catch (e) {
            console.error('Failed to load filter options:', e);
          }
        },

        loadResults: async function () {
          if (!this.modelId) return;
          this.loading = true;
          try {
            var result = await this.$api.get('filtered-pages/results', {
              model_id:  this.modelId,
              template:  this.template,
              filters:   JSON.stringify(this.filterDefs),
              columns:   JSON.stringify(this.columnDefs),
              active:    JSON.stringify(this.active),
              search:    this.search,
              sort:      this.currentSort,
              page:      this.currentPage,
              page_size: this.pageSize
            });
            this.items      = result.items      || [];
            this.total      = result.total      || 0;
            this.totalPages = result.totalPages || 0;
            this.saveState();
          } catch (e) {
            console.error('Failed to load filtered pages results:', e);
          } finally {
            this.loading = false;
          }
        },

        onFilterChange: function () {
          this.currentPage = 1;
          this.loadResults();
        },

        onSearchInput: function () {
          var self = this;
          clearTimeout(self._searchTimer);
          self._searchTimer = setTimeout(function () {
            self.currentPage = 1;
            self.loadResults();
          }, 300);
        },

        goToPage: function (page) {
          if (page < 1 || page > this.totalPages) return;
          this.currentPage = page;
          this.loadResults();
        },

        changeSort: function (field) {
          var parts = this.currentSort.split(' ');
          if (parts[0] === field) {
            this.currentSort = field + ' ' + (parts[1] === 'asc' ? 'desc' : 'asc');
          } else {
            this.currentSort = field + ' asc';
          }
          this.currentPage = 1;
          this.loadResults();
        },

        sortIcon: function (field) {
          var parts = this.currentSort.split(' ');
          if (parts[0] !== field) return '\u2195';
          return parts[1] === 'asc' ? '\u2191' : '\u2193';
        },

        isSortable: function (field) {
          return this.sortOptions.some(function (opt) { return opt.field === field; });
        },

        colWidth: function (width) {
          if (!width) return 'auto';
          var parts = String(width).split('/');
          if (parts.length === 2) {
            return (parseInt(parts[0]) / parseInt(parts[1]) * 100).toFixed(2) + '%';
          }
          return width;
        },

        colFlex: function (width) {
          if (!width) return '1 1 0';
          var parts = String(width).split('/');
          if (parts.length === 2) {
            return '0 0 ' + (parseInt(parts[0]) / parseInt(parts[1]) * 100).toFixed(2) + '%';
          }
          return '0 0 ' + width;
        },

        resetFilters: function () {
          this.active      = {};
          this.search      = '';
          this.currentPage = 1;
          this.loadResults();
        },

        navigateTo: function (url) {
          window.location.href = url;
        },

        displayValue: function (item, col) {
          if (col.field === 'title')  return item.title;
          if (col.field === 'status') return item.status;
          return (item.displayValues && item.displayValues[col.field]) || '';
        },

        isExcludeFilter: function (field) {
          return (this.filterDefs[field] && this.filterDefs[field].mode === 'exclude');
        },

        filterDefaultLabel: function (field) {
          var label = this.filterDefs[field] ? this.filterDefs[field].label : field;
          return this.isExcludeFilter(field) ? 'Exclude ' + label + ': None' : label + ': All';
        }
      },

      template: `
        <section class="k-section k-filteredpages-section">

          <style>
            .k-filteredpages-section .k-fp-item:hover { background: var(--color-gray-200) !important; }
          </style>

          <!-- Header -->
          <header class="k-section-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
            <h2 class="k-headline">{{ headline }}</h2>
            <k-button
              v-if="modelId"
              :dialog="'pages/create?parent=pages/' + modelId.split('/').join('+') + (template ? '&template=' + template : '')"
              icon="add"
              text="Add"
              size="sm"
              variant="filled"
            />
          </header>

          <!-- Filter bar: distinct lighter background -->
          <div style="background:var(--color-gray-100);border-radius:var(--rounded);padding:0.65rem 0.75rem;margin-bottom:0.75rem;">
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">
              <input
                v-if="searchEnabled"
                type="text"
                v-model="search"
                @input="onSearchInput"
                placeholder="Search..."
                style="flex:1;min-width:160px;padding:0.4rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.875rem;background:var(--color-white);color:var(--color-text);"
              />
              <select
                v-for="field in filterFields"
                :key="field"
                v-model="active[field]"
                @change="onFilterChange"
                :style="'padding:0.4rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.875rem;background:var(--color-white);color:var(--color-text);cursor:pointer;' + (isExcludeFilter(field) && active[field] ? 'border-color:var(--color-negative,#c82829);' : '')"
              >
                <option value="">{{ filterDefaultLabel(field) }}</option>
                <option v-for="opt in (options[field] || [])" :key="opt.value" :value="opt.value">{{ opt.text }}</option>
              </select>
              <button
                v-if="hasActiveFilters || search"
                @click="resetFilters"
                style="padding:0.35rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.8rem;background:var(--color-white);color:var(--color-text-dimmed);cursor:pointer;"
              >Clear</button>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="loading" style="padding:1rem 0;color:var(--color-text-dimmed);font-size:0.875rem;">Loading&hellip;</div>

          <!-- Results list -->
          <template v-else-if="items.length > 0">

            <!-- Column header row -->
            <div v-if="columnDefs.length > 1 || sortOptions.length > 0" style="display:flex;align-items:center;padding:0.25rem 0.75rem;margin-bottom:0.15rem;">
              <div
                v-for="col in columnDefs"
                :key="col.field"
                :style="'flex:' + colFlex(col.width) + ';min-width:0;font-size:0.7rem;font-weight:600;color:var(--color-text-dimmed);text-transform:uppercase;letter-spacing:0.05em;padding:0 0;' + (isSortable(col.field) ? 'cursor:pointer;user-select:none;' : '')"
                @click="isSortable(col.field) && changeSort(col.field)"
              >
                {{ col.label }}<span v-if="isSortable(col.field)" style="opacity:0.5;margin-left:0.2rem;">{{ sortIcon(col.field) }}</span>
              </div>
            </div>

            <!-- Item cards -->
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
              <div
                v-for="item in items"
                :key="item.id"
                class="k-fp-item"
                style="display:flex;align-items:center;background:var(--color-white);border-radius:var(--rounded);overflow:hidden;cursor:pointer;"
                @click="navigateTo(item.panelUrl)"
              >
                <div
                  v-for="(col, idx) in columnDefs"
                  :key="col.field"
                  :style="'flex:' + colFlex(col.width) + ';min-width:0;display:flex;align-items:center;padding:0.75rem;font-size:0.875rem;overflow:hidden;'"
                >
                  <a v-if="idx === 0" :href="item.panelUrl" @click.stop
                     style="color:var(--color-text);text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ displayValue(item, col) }}</a>
                  <span v-else style="color:var(--color-text-dimmed);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ displayValue(item, col) }}</span>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" style="display:flex;align-items:center;gap:1rem;padding:0.75rem 0;font-size:0.875rem;">
              <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                      :style="'padding:0.35rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.8rem;background:transparent;cursor:pointer;opacity:' + (currentPage <= 1 ? '0.4' : '1') + ';'"
              >&larr; Prev</button>
              <span style="color:var(--color-text-dimmed);">Page {{ currentPage }} of {{ totalPages }} &middot; {{ total }} total</span>
              <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                      :style="'padding:0.35rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.8rem;background:transparent;cursor:pointer;opacity:' + (currentPage >= totalPages ? '0.4' : '1') + ';'"
              >Next &rarr;</button>
            </div>
          </template>

          <!-- Empty state -->
          <k-empty v-else icon="page">
            No pages found{{ hasActiveFilters || search ? ' matching the current filters' : '' }}.
          </k-empty>

        </section>
      `
    },

    filteredfiles: {
      data: function () {
        return {
          headline:      '',
          modelId:       '',
          apiEndpoint:   'filtered-files',
          filterDefs:    {},
          columnDefs:    [],
          pageSize:      25,
          searchEnabled: false,
          defaultSort:   'filename asc',
          sortOptions:   [],
          uploadTemplate: 'image',

          active:      {},
          search:      '',
          currentSort: 'filename asc',
          currentPage: 1,

          options: {},

          items:      [],
          total:      0,
          totalPages: 0,
          loading:    false,

          _searchTimer: null
        };
      },

      created: async function () {
        try {
          var response = await this.load();
          this.headline      = response.headline             || '';
          this.modelId       = response.modelId             || '';
          this.apiEndpoint    = response.resolvedApiEndpoint  || 'filtered-files';
          this.uploadTemplate = response.uploadTemplate      || 'image';
          this.filterDefs    = response.filters             || {};
          this.columnDefs    = response.columns             || [];
          this.pageSize      = response.pageSize            || 25;
          this.searchEnabled = response.search              || false;
          this.defaultSort   = response.sortBy              || 'filename asc';
          this.sortOptions   = response.sortOptions         || [];
          this.currentSort   = response.sortBy              || 'filename asc';

          var initialActive = {};
          Object.keys(this.filterDefs).forEach(function (field) { initialActive[field] = ''; });
          this.active = initialActive;

          var saved = this.loadSavedState();
          if (saved.active)  Object.assign(this.active, saved.active);
          if (saved.search)  this.search      = saved.search;
          if (saved.sort)    this.currentSort = saved.sort;
          if (saved.page)    this.currentPage = saved.page;

          await Promise.all([this.loadOptions(), this.loadResults()]);
        } catch (error) {
          console.error('Failed to initialise filteredfiles section:', error);
        }
      },

      computed: {
        filterFields: function () {
          return Object.keys(this.filterDefs);
        },
        sortField: function () {
          return this.currentSort.split(' ')[0] || 'filename';
        },
        sortDir: function () {
          return (this.currentSort.split(' ')[1] || 'asc');
        },
        hasActiveFilters: function () {
          var self = this;
          return Object.keys(self.active).some(function (k) { return self.active[k] !== ''; });
        }
      },

      methods: {
        loadSavedState: function () {
          try {
            return JSON.parse(localStorage.getItem('bsbi-filteredfiles-' + this.modelId) || '{}');
          } catch (e) {
            return {};
          }
        },

        saveState: function () {
          try {
            localStorage.setItem('bsbi-filteredfiles-' + this.modelId, JSON.stringify({
              active:  this.active,
              search:  this.search,
              sort:    this.currentSort,
              page:    this.currentPage
            }));
          } catch (e) { /* storage unavailable */ }
        },

        loadOptions: async function () {
          try {
            this.options = await this.$api.get(this.apiEndpoint + '/options', {
              model_id: this.modelId,
              filters:  JSON.stringify(this.filterDefs)
            });
          } catch (e) {
            console.error('Failed to load filter options:', e);
          }
        },

        loadResults: async function () {
          if (!this.modelId) return;
          this.loading = true;
          try {
            var result = await this.$api.get(this.apiEndpoint + '/results', {
              model_id:  this.modelId,
              filters:   JSON.stringify(this.filterDefs),
              columns:   JSON.stringify(this.columnDefs),
              active:    JSON.stringify(this.active),
              search:    this.search,
              sort:      this.currentSort,
              page:      this.currentPage,
              page_size: this.pageSize
            });
            this.items      = result.items      || [];
            this.total      = result.total      || 0;
            this.totalPages = result.totalPages || 0;
            this.saveState();
          } catch (e) {
            console.error('Failed to load filtered files results:', e);
          } finally {
            this.loading = false;
          }
        },

        onFilterChange: function () {
          this.currentPage = 1;
          this.loadResults();
        },

        onSearchInput: function () {
          var self = this;
          clearTimeout(self._searchTimer);
          self._searchTimer = setTimeout(function () {
            self.currentPage = 1;
            self.loadResults();
          }, 300);
        },

        goToPage: function (page) {
          if (page < 1 || page > this.totalPages) return;
          this.currentPage = page;
          this.loadResults();
        },

        changeSort: function (field) {
          var parts = this.currentSort.split(' ');
          if (parts[0] === field) {
            this.currentSort = field + ' ' + (parts[1] === 'asc' ? 'desc' : 'asc');
          } else {
            this.currentSort = field + ' asc';
          }
          this.currentPage = 1;
          this.loadResults();
        },

        sortIcon: function (field) {
          var parts = this.currentSort.split(' ');
          if (parts[0] !== field) return '\u2195';
          return parts[1] === 'asc' ? '\u2191' : '\u2193';
        },

        isSortable: function (field) {
          return this.sortOptions.some(function (opt) { return opt.field === field; });
        },

        colWidth: function (width) {
          if (!width) return 'auto';
          var parts = String(width).split('/');
          if (parts.length === 2) {
            return (parseInt(parts[0]) / parseInt(parts[1]) * 100).toFixed(2) + '%';
          }
          return width;
        },

        colFlex: function (width) {
          if (!width) return '1 1 0';
          var parts = String(width).split('/');
          if (parts.length === 2) {
            return '0 0 ' + (parseInt(parts[0]) / parseInt(parts[1]) * 100).toFixed(2) + '%';
          }
          return '0 0 ' + width;
        },

        resetFilters: function () {
          var initialActive = {};
          Object.keys(this.filterDefs).forEach(function (field) { initialActive[field] = ''; });
          this.active      = initialActive;
          this.search      = '';
          this.currentPage = 1;
          this.loadResults();
        },

        navigateTo: function (url) {
          window.location.href = url;
        },

        displayValue: function (item, col) {
          if (col.field === 'filename') return item.filename;
          if (col.field === 'title')    return item.title;
          return (item.displayValues && item.displayValues[col.field]) || '';
        },

        isExcludeFilter: function (field) {
          return (this.filterDefs[field] && this.filterDefs[field].mode === 'exclude');
        },

        filterDefaultLabel: function (field) {
          var label = this.filterDefs[field] ? this.filterDefs[field].label : field;
          return this.isExcludeFilter(field) ? 'Exclude ' + label + ': None' : label + ': All';
        },

        onDialogSuccess: function () {
          this.loadResults();
          this.loadOptions();
        }
      },

      template: `
        <section class="k-section k-filteredfiles-section">

          <style>
            .k-filteredfiles-section .k-ff-item:hover { background: var(--color-gray-200) !important; }
          </style>

          <!-- Header -->
          <header class="k-section-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
            <h2 class="k-headline">{{ headline }}</h2>
            <k-button
              v-if="modelId"
              icon="upload"
              text="Add"
              size="sm"
              variant="filled"
              :dialog="'files/upload?parent=pages/' + modelId.split('/').join('+') + '&template=' + uploadTemplate"
            />
          </header>

          <!-- Filter bar: distinct lighter background to stand out from results -->
          <div style="background:var(--color-gray-100);border-radius:var(--rounded);padding:0.65rem 0.75rem;margin-bottom:0.75rem;">
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">
              <input
                v-if="searchEnabled"
                type="text"
                v-model="search"
                @input="onSearchInput"
                placeholder="Search..."
                style="flex:1;min-width:160px;padding:0.4rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.875rem;background:var(--color-white);color:var(--color-text);"
              />
              <select
                v-for="field in filterFields"
                :key="field"
                v-model="active[field]"
                @change="onFilterChange"
                :style="'padding:0.4rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.875rem;background:var(--color-white);color:var(--color-text);cursor:pointer;' + (isExcludeFilter(field) && active[field] ? 'border-color:var(--color-negative,#c82829);' : '')"
              >
                <option value="">{{ filterDefaultLabel(field) }}</option>
                <option v-for="opt in (options[field] || [])" :key="opt.value" :value="opt.value">{{ opt.text }}</option>
              </select>
              <button
                v-if="hasActiveFilters || search"
                @click="resetFilters"
                style="padding:0.35rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.8rem;background:var(--color-white);color:var(--color-text-dimmed);cursor:pointer;"
              >Clear</button>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="loading" style="padding:1rem 0;color:var(--color-text-dimmed);font-size:0.875rem;">Loading&hellip;</div>

          <!-- Results list -->
          <template v-else-if="items.length > 0">

            <!-- Column header row (only shown when there are sortable columns or multiple columns) -->
            <div v-if="columnDefs.length > 1 || sortOptions.length > 0" style="display:flex;align-items:center;padding:0.25rem 0.75rem 0.25rem 0;margin-bottom:0.15rem;">
              <div style="width:64px;flex-shrink:0;"></div>
              <div
                v-for="col in columnDefs"
                :key="col.field"
                :style="'flex:' + colFlex(col.width) + ';min-width:0;font-size:0.7rem;font-weight:600;color:var(--color-text-dimmed);text-transform:uppercase;letter-spacing:0.05em;padding:0 0.5rem;' + (isSortable(col.field) ? 'cursor:pointer;user-select:none;' : '')"
                @click="isSortable(col.field) && changeSort(col.field)"
              >
                {{ col.label }}<span v-if="isSortable(col.field)" style="opacity:0.5;margin-left:0.2rem;">{{ sortIcon(col.field) }}</span>
              </div>
            </div>

            <!-- Item cards -->
            <div style="display:flex;flex-direction:column;gap:0.25rem;">
              <div
                v-for="item in items"
                :key="item.id"
                class="k-ff-item"
                style="display:flex;align-items:stretch;background:var(--color-white);border-radius:var(--rounded);overflow:hidden;cursor:pointer;"
                @click="navigateTo(item.panelUrl)"
              >
                <!-- Thumbnail: flush left, full row height -->
                <div style="width:64px;flex-shrink:0;overflow:hidden;">
                  <div
                    :style="'width:64px;height:100%;min-height:56px;background-color:var(--color-gray-200);' + (item.thumbUrl ? 'background-image:url(' + JSON.stringify(item.thumbUrl) + ');background-size:cover;background-position:center;' : '')"
                  ></div>
                </div>
                <!-- Column cells -->
                <div
                  v-for="(col, idx) in columnDefs"
                  :key="col.field"
                  :style="'flex:' + colFlex(col.width) + ';min-width:0;display:flex;align-items:center;padding:0.75rem 0.75rem;font-size:0.875rem;overflow:hidden;'"
                >
                  <a v-if="idx === 0" :href="item.panelUrl" @click.stop
                     style="color:var(--color-text);text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ displayValue(item, col) }}</a>
                  <span v-else style="color:var(--color-text-dimmed);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ displayValue(item, col) }}</span>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" style="display:flex;align-items:center;gap:1rem;padding:0.75rem 0;font-size:0.875rem;">
              <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                      :style="'padding:0.35rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.8rem;background:transparent;cursor:pointer;opacity:' + (currentPage <= 1 ? '0.4' : '1') + ';'"
              >&larr; Prev</button>
              <span style="color:var(--color-text-dimmed);">Page {{ currentPage }} of {{ totalPages }} &middot; {{ total }} total</span>
              <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                      :style="'padding:0.35rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.8rem;background:transparent;cursor:pointer;opacity:' + (currentPage >= totalPages ? '0.4' : '1') + ';'"
              >Next &rarr;</button>
            </div>
          </template>

          <!-- Empty state -->
          <k-empty v-else icon="image">
            No images found{{ hasActiveFilters || search ? ' matching the current filters' : '' }}.
          </k-empty>

        </section>
      `
    },

    translatedpages: {
      data: function () {
        return {
          translations: []
        }
      },
      created: async function () {
        try {
          const response = await this.load();
          this.translations = response.translations;
        } catch (error) {
          console.error("Failed to load translated pages section:", error);
        }
      },
      template: `
        <div v-if="translations.length > 0" style="margin-bottom: 1.5rem;">
          <section class="k-section k-translated-pages-section">
            <header class="k-section-header">
              <h2 class="k-headline">Translated versions</h2>
            </header>
            <div class="k-items" data-layout="list">
              <k-item
                v-for="t in translations"
                :key="t.code"
                :text="t.name"
                :info="t.code"
                :link="t.panelUrl"
                layout="list"
                :image="false"
              />
            </div>
          </section>
        </div>
      `
    },

    imagebankindexstats: {
      data: function () {
        return {
          headline: null,
          stats: null,
          rebuilding: false
        }
      },
      created: async function () {
        try {
          const response = await this.load();
          this.headline = response.headline;
          this.stats = response.stats;
        } catch (error) {
          console.error('Failed to load imageBank index stats:', error);
        }
      },
      methods: {
        rebuild: async function () {
          this.rebuilding = true;
          try {
            await fetch('/imagebank-index-rebuild');
            const response = await this.load();
            this.stats = response.stats;
          } catch (error) {
            console.error('Failed to rebuild imageBank index:', error);
          } finally {
            this.rebuilding = false;
          }
        }
      },
      template: `
        <section class="k-section k-imagebankindexstats-section">
          <header class="k-section-header">
            <h2 class="k-headline">{{ headline }}</h2>
          </header>

          <div v-if="stats" style="padding: 0.75rem 0 0.5rem;">
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 0.75rem; padding: 0.75rem 1rem; background: var(--color-background); border-radius: var(--rounded);">
              <div style="color: var(--color-text-dimmed); font-size: 0.875rem;">
                {{ stats.total_files }} files indexed
              </div>
              <div style="color: var(--color-text-dimmed); font-size: 0.875rem;">
                {{ stats.total_taxa }} taxa linked
              </div>
              <div style="color: var(--color-text-dimmed); font-size: 0.875rem;">
                Last rebuilt: {{ stats.last_rebuild || 'Never' }}
              </div>
              <div style="margin-left: auto;">
                <button
                  @click="rebuild"
                  :disabled="rebuilding"
                  style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.75rem; background: var(--color-black); color: var(--color-white); border: none; cursor: pointer; border-radius: var(--rounded); font-size: 0.8rem;"
                >
                  <span v-if="rebuilding" style="display: inline-block; width: 0.8rem; height: 0.8rem; border: 2px solid var(--color-white); border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span>
                  {{ rebuilding ? 'Rebuilding...' : 'Rebuild' }}
                </button>
              </div>
            </div>
          </div>

          <k-empty v-else icon="image">
            Image bank index not yet built. Click Rebuild to create it.
            <button
              @click="rebuild"
              :disabled="rebuilding"
              style="display: inline-flex; align-items: center; gap: 0.3rem; margin-top: 0.75rem; padding: 0.4rem 0.75rem; background: var(--color-black); color: var(--color-white); border: none; cursor: pointer; border-radius: var(--rounded); font-size: 0.8rem;"
            >
              <span v-if="rebuilding" style="display: inline-block; width: 0.8rem; height: 0.8rem; border: 2px solid var(--color-white); border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite;"></span>
              {{ rebuilding ? 'Rebuilding...' : 'Rebuild' }}
            </button>
          </k-empty>

          <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        </section>
      `
    }
  },

  components: {
    'k-index-stats-view': {
      props: ['searchStats', 'contentIndexes', 'imageBankStats'],
      data: function () {
        return {
          currentSearchStats: this.searchStats,
          currentContentIndexes: this.contentIndexes,
          currentImageBankStats: this.imageBankStats,
          rebuilding: null,
          shownRecords: {},
          recordData: {},
          loadingRecords: {}
        }
      },
      methods: {
        rebuild: async function (type, name) {
          this.rebuilding = type === 'content' ? name : type;
          try {
            if (type === 'search') {
              await fetch('/search-rebuild');
              const res = await fetch('/search-stats');
              this.currentSearchStats = await res.json();
            } else if (type === 'content') {
              await fetch('/content-index-rebuild?name=' + encodeURIComponent(name));
              const res = await fetch('/content-index-stats');
              this.currentContentIndexes = await res.json();
            } else if (type === 'imagebank') {
              await fetch('/imagebank-index-rebuild');
              const res = await fetch('/imagebank-index-stats');
              this.currentImageBankStats = await res.json();
            }
          } catch (error) {
            console.error('Index rebuild failed:', error);
          } finally {
            this.rebuilding = null;
          }
        },

        toggleRecords: async function (key, type, name) {
          if (this.shownRecords[key]) {
            this.shownRecords[key] = false;
            this.recordData[key] = null;
          } else {
            await this.fetchRecords(key, type, name, 1);
            this.shownRecords[key] = true;
          }
        },

        fetchRecords: async function (key, type, name, page) {
          this.loadingRecords[key] = true;
          try {
            var url;
            if (type === 'search') {
              url = '/search-index-records?page=' + page;
            } else if (type === 'content') {
              url = '/content-index-records?name=' + encodeURIComponent(name) + '&page=' + page;
            } else {
              url = '/imagebank-index-records?page=' + page;
            }
            var res = await fetch(url);
            this.recordData[key] = await res.json();
          } catch (e) {
            console.error('Failed to fetch index records:', e);
          } finally {
            this.loadingRecords[key] = false;
          }
        },

        goToPage: async function (key, type, name, page) {
          await this.fetchRecords(key, type, name, page);
        }
      },
      template: `
        <k-panel-inside class="k-index-stats-view">
          <k-header>Indexes</k-header>
          <style>@keyframes k-idx-spin { to { transform: rotate(360deg); } }</style>

          <div style="display: flex; flex-direction: column; gap: 1rem; padding: 1.5rem 0;">

            <!-- Search Index -->
            <div style="background: var(--color-background); border-radius: var(--rounded); padding: 1.25rem;">
              <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <h2 style="font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-dimmed); min-width: 120px; margin: 0;">Search Index</h2>
                <span v-if="currentSearchStats" style="font-size: 0.85rem; color: var(--color-text-dimmed);">{{ currentSearchStats.total_pages }} pages &middot; Last rebuilt: {{ currentSearchStats.last_rebuild || 'Never' }}</span>
                <span v-else style="font-size: 0.85rem; color: var(--color-text-dimmed);">Not yet built</span>
                <div style="margin-left: auto; display: flex; gap: 0.5rem; flex-shrink: 0;">
                  <button
                    v-if="currentSearchStats"
                    @click="toggleRecords('search', 'search', null)"
                    :disabled="loadingRecords['search']"
                    style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.6rem; background: transparent; color: var(--color-text); border: 1px solid var(--color-border); cursor: pointer; border-radius: var(--rounded); font-size: 0.75rem;"
                  >
                    <span v-if="loadingRecords['search']" style="display: inline-block; width: 0.65rem; height: 0.65rem; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: k-idx-spin 0.6s linear infinite;"></span>
                    {{ shownRecords['search'] ? 'Hide' : 'Show' }}
                  </button>
                  <button
                    @click="rebuild('search')"
                    :disabled="rebuilding !== null"
                    style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.6rem; background: var(--color-black); color: var(--color-white); border: none; cursor: pointer; border-radius: var(--rounded); font-size: 0.75rem;"
                    :style="{ opacity: rebuilding !== null && rebuilding !== 'search' ? '0.4' : '1' }"
                  >
                    <span v-if="rebuilding === 'search'" style="display: inline-block; width: 0.65rem; height: 0.65rem; border: 2px solid var(--color-white); border-top-color: transparent; border-radius: 50%; animation: k-idx-spin 0.6s linear infinite;"></span>
                    {{ rebuilding === 'search' ? 'Rebuilding...' : 'Rebuild' }}
                  </button>
                </div>
              </div>
              <div v-if="shownRecords['search'] && recordData['search']" style="margin-top: 1rem; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                  <thead>
                    <tr style="border-bottom: 2px solid var(--color-border);">
                      <th v-for="col in recordData['search'].columns" :key="col" style="text-align: left; padding: 0.4rem 0.5rem; color: var(--color-text-dimmed); font-weight: 600; white-space: nowrap;">{{ col }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, i) in recordData['search'].rows" :key="i" style="border-bottom: 1px solid var(--color-border);">
                      <td v-for="col in recordData['search'].columns" :key="col" style="padding: 0.4rem 0.5rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ row[col] }}</td>
                    </tr>
                  </tbody>
                </table>
                <div v-if="recordData['search'].totalPages > 1" style="display: flex; align-items: center; gap: 1rem; padding: 0.5rem 0; font-size: 0.8rem; color: var(--color-text-dimmed);">
                  <button @click="goToPage('search', 'search', null, recordData['search'].page - 1)" :disabled="recordData['search'].page <= 1" style="padding: 0.25rem 0.5rem; border: 1px solid var(--color-border); border-radius: var(--rounded); background: transparent; cursor: pointer; font-size: 0.75rem;" :style="{ opacity: recordData['search'].page <= 1 ? '0.4' : '1' }">&larr; Prev</button>
                  <span>Page {{ recordData['search'].page }} of {{ recordData['search'].totalPages }} &middot; {{ recordData['search'].total }} total</span>
                  <button @click="goToPage('search', 'search', null, recordData['search'].page + 1)" :disabled="recordData['search'].page >= recordData['search'].totalPages" style="padding: 0.25rem 0.5rem; border: 1px solid var(--color-border); border-radius: var(--rounded); background: transparent; cursor: pointer; font-size: 0.75rem;" :style="{ opacity: recordData['search'].page >= recordData['search'].totalPages ? '0.4' : '1' }">Next &rarr;</button>
                </div>
              </div>
            </div>

            <!-- Content Indexes -->
            <template v-if="currentContentIndexes && currentContentIndexes.length > 0">
              <div v-for="idx in currentContentIndexes" :key="idx.name" style="background: var(--color-background); border-radius: var(--rounded); padding: 1.25rem;">
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                  <h2 style="font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-dimmed); min-width: 120px; margin: 0;">{{ idx.name }}</h2>
                  <span style="font-size: 0.85rem; color: var(--color-text-dimmed);">{{ idx.total_rows }} rows &middot; Last rebuilt: {{ idx.last_rebuild || 'Never' }}</span>
                  <div style="margin-left: auto; display: flex; gap: 0.5rem; flex-shrink: 0;">
                    <button
                      @click="toggleRecords('content:' + idx.name, 'content', idx.name)"
                      :disabled="loadingRecords['content:' + idx.name]"
                      style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.6rem; background: transparent; color: var(--color-text); border: 1px solid var(--color-border); cursor: pointer; border-radius: var(--rounded); font-size: 0.75rem;"
                    >
                      <span v-if="loadingRecords['content:' + idx.name]" style="display: inline-block; width: 0.65rem; height: 0.65rem; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: k-idx-spin 0.6s linear infinite;"></span>
                      {{ shownRecords['content:' + idx.name] ? 'Hide' : 'Show' }}
                    </button>
                    <button
                      @click="rebuild('content', idx.name)"
                      :disabled="rebuilding !== null"
                      style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.6rem; background: var(--color-black); color: var(--color-white); border: none; cursor: pointer; border-radius: var(--rounded); font-size: 0.75rem;"
                      :style="{ opacity: rebuilding !== null && rebuilding !== idx.name ? '0.4' : '1' }"
                    >
                      <span v-if="rebuilding === idx.name" style="display: inline-block; width: 0.65rem; height: 0.65rem; border: 2px solid var(--color-white); border-top-color: transparent; border-radius: 50%; animation: k-idx-spin 0.6s linear infinite;"></span>
                      {{ rebuilding === idx.name ? 'Rebuilding...' : 'Rebuild' }}
                    </button>
                  </div>
                </div>
                <div v-if="shownRecords['content:' + idx.name] && recordData['content:' + idx.name]" style="margin-top: 1rem; overflow-x: auto;">
                  <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                    <thead>
                      <tr style="border-bottom: 2px solid var(--color-border);">
                        <th v-for="col in recordData['content:' + idx.name].columns" :key="col" style="text-align: left; padding: 0.4rem 0.5rem; color: var(--color-text-dimmed); font-weight: 600; white-space: nowrap;">{{ col }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(row, i) in recordData['content:' + idx.name].rows" :key="i" style="border-bottom: 1px solid var(--color-border);">
                        <td v-for="col in recordData['content:' + idx.name].columns" :key="col" style="padding: 0.4rem 0.5rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ row[col] }}</td>
                      </tr>
                    </tbody>
                  </table>
                  <div v-if="recordData['content:' + idx.name].totalPages > 1" style="display: flex; align-items: center; gap: 1rem; padding: 0.5rem 0; font-size: 0.8rem; color: var(--color-text-dimmed);">
                    <button @click="goToPage('content:' + idx.name, 'content', idx.name, recordData['content:' + idx.name].page - 1)" :disabled="recordData['content:' + idx.name].page <= 1" style="padding: 0.25rem 0.5rem; border: 1px solid var(--color-border); border-radius: var(--rounded); background: transparent; cursor: pointer; font-size: 0.75rem;" :style="{ opacity: recordData['content:' + idx.name].page <= 1 ? '0.4' : '1' }">&larr; Prev</button>
                    <span>Page {{ recordData['content:' + idx.name].page }} of {{ recordData['content:' + idx.name].totalPages }} &middot; {{ recordData['content:' + idx.name].total }} total</span>
                    <button @click="goToPage('content:' + idx.name, 'content', idx.name, recordData['content:' + idx.name].page + 1)" :disabled="recordData['content:' + idx.name].page >= recordData['content:' + idx.name].totalPages" style="padding: 0.25rem 0.5rem; border: 1px solid var(--color-border); border-radius: var(--rounded); background: transparent; cursor: pointer; font-size: 0.75rem;" :style="{ opacity: recordData['content:' + idx.name].page >= recordData['content:' + idx.name].totalPages ? '0.4' : '1' }">Next &rarr;</button>
                  </div>
                </div>
              </div>
            </template>
            <div v-else style="background: var(--color-background); border-radius: var(--rounded); padding: 1.25rem;">
              <k-empty icon="table">No content indexes registered</k-empty>
            </div>

            <!-- Image Bank Index -->
            <div style="background: var(--color-background); border-radius: var(--rounded); padding: 1.25rem;">
              <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <h2 style="font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-dimmed); min-width: 120px; margin: 0;">Image Bank Index</h2>
                <span v-if="currentImageBankStats" style="font-size: 0.85rem; color: var(--color-text-dimmed);">{{ currentImageBankStats.total_files }} files &middot; {{ currentImageBankStats.total_taxa }} taxa &middot; Last rebuilt: {{ currentImageBankStats.last_rebuild || 'Never' }}</span>
                <span v-else style="font-size: 0.85rem; color: var(--color-text-dimmed);">Not yet built</span>
                <div style="margin-left: auto; display: flex; gap: 0.5rem; flex-shrink: 0;">
                  <button
                    v-if="currentImageBankStats"
                    @click="toggleRecords('imagebank', 'imagebank', null)"
                    :disabled="loadingRecords['imagebank']"
                    style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.6rem; background: transparent; color: var(--color-text); border: 1px solid var(--color-border); cursor: pointer; border-radius: var(--rounded); font-size: 0.75rem;"
                  >
                    <span v-if="loadingRecords['imagebank']" style="display: inline-block; width: 0.65rem; height: 0.65rem; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: k-idx-spin 0.6s linear infinite;"></span>
                    {{ shownRecords['imagebank'] ? 'Hide' : 'Show' }}
                  </button>
                  <button
                    @click="rebuild('imagebank')"
                    :disabled="rebuilding !== null"
                    style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.3rem 0.6rem; background: var(--color-black); color: var(--color-white); border: none; cursor: pointer; border-radius: var(--rounded); font-size: 0.75rem;"
                    :style="{ opacity: rebuilding !== null && rebuilding !== 'imagebank' ? '0.4' : '1' }"
                  >
                    <span v-if="rebuilding === 'imagebank'" style="display: inline-block; width: 0.65rem; height: 0.65rem; border: 2px solid var(--color-white); border-top-color: transparent; border-radius: 50%; animation: k-idx-spin 0.6s linear infinite;"></span>
                    {{ rebuilding === 'imagebank' ? 'Rebuilding...' : 'Rebuild' }}
                  </button>
                </div>
              </div>
              <div v-if="shownRecords['imagebank'] && recordData['imagebank']" style="margin-top: 1rem; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                  <thead>
                    <tr style="border-bottom: 2px solid var(--color-border);">
                      <th v-for="col in recordData['imagebank'].columns" :key="col" style="text-align: left; padding: 0.4rem 0.5rem; color: var(--color-text-dimmed); font-weight: 600; white-space: nowrap;">{{ col }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, i) in recordData['imagebank'].rows" :key="i" style="border-bottom: 1px solid var(--color-border);">
                      <td v-for="col in recordData['imagebank'].columns" :key="col" style="padding: 0.4rem 0.5rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ row[col] }}</td>
                    </tr>
                  </tbody>
                </table>
                <div v-if="recordData['imagebank'].totalPages > 1" style="display: flex; align-items: center; gap: 1rem; padding: 0.5rem 0; font-size: 0.8rem; color: var(--color-text-dimmed);">
                  <button @click="goToPage('imagebank', 'imagebank', null, recordData['imagebank'].page - 1)" :disabled="recordData['imagebank'].page <= 1" style="padding: 0.25rem 0.5rem; border: 1px solid var(--color-border); border-radius: var(--rounded); background: transparent; cursor: pointer; font-size: 0.75rem;" :style="{ opacity: recordData['imagebank'].page <= 1 ? '0.4' : '1' }">&larr; Prev</button>
                  <span>Page {{ recordData['imagebank'].page }} of {{ recordData['imagebank'].totalPages }} &middot; {{ recordData['imagebank'].total }} total</span>
                  <button @click="goToPage('imagebank', 'imagebank', null, recordData['imagebank'].page + 1)" :disabled="recordData['imagebank'].page >= recordData['imagebank'].totalPages" style="padding: 0.25rem 0.5rem; border: 1px solid var(--color-border); border-radius: var(--rounded); background: transparent; cursor: pointer; font-size: 0.75rem;" :style="{ opacity: recordData['imagebank'].page >= recordData['imagebank'].totalPages ? '0.4' : '1' }">Next &rarr;</button>
                </div>
              </div>
            </div>

          </div>
        </k-panel-inside>
      `
    }
  }
});
