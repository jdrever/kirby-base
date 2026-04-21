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
        }
      },

      template: `
        <section class="k-section k-filteredpages-section">

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

          <!-- Toolbar: search + filter dropdowns + clear button -->
          <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem;align-items:center;">
            <input
              v-if="searchEnabled"
              type="text"
              v-model="search"
              @input="onSearchInput"
              placeholder="Search..."
              style="flex:1;min-width:160px;padding:0.4rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.875rem;background:var(--color-background);color:var(--color-text);"
            />
            <select
              v-for="field in filterFields"
              :key="field"
              v-model="active[field]"
              @change="onFilterChange"
              style="padding:0.4rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.875rem;background:var(--color-background);color:var(--color-text);cursor:pointer;"
            >
              <option value="">{{ filterDefs[field].label }}: All</option>
              <option v-for="opt in (options[field] || [])" :key="opt.value" :value="opt.value">{{ opt.text }}</option>
            </select>
            <button
              v-if="hasActiveFilters || search"
              @click="resetFilters"
              style="padding:0.35rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.8rem;background:transparent;color:var(--color-text-dimmed);cursor:pointer;"
            >Clear</button>
          </div>

          <!-- Loading -->
          <div v-if="loading" style="padding:1rem 0;color:var(--color-text-dimmed);font-size:0.875rem;">Loading&hellip;</div>

          <!-- Results table -->
          <template v-else-if="items.length > 0">
            <table style="width:100%;border-collapse:collapse;">
              <thead>
                <tr style="border-bottom:2px solid var(--color-border);">
                  <th
                    v-for="col in columnDefs"
                    :key="col.field"
                    :style="'text-align:left;padding:0.5rem 0.75rem;font-size:0.75rem;color:var(--color-text-dimmed);font-weight:600;width:' + colWidth(col.width) + ';' + (isSortable(col.field) ? 'cursor:pointer;user-select:none;' : '')"
                    @click="isSortable(col.field) && changeSort(col.field)"
                  >
                    {{ col.label }}<span v-if="isSortable(col.field)" style="opacity:0.6;margin-left:0.25rem;">{{ sortIcon(col.field) }}</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in items"
                  :key="item.id"
                  style="border-bottom:1px solid var(--color-border);cursor:pointer;"
                  @click="navigateTo(item.panelUrl)"
                >
                  <td
                    v-for="(col, idx) in columnDefs"
                    :key="col.field"
                    style="padding:0.6rem 0.75rem;font-size:0.875rem;"
                  >
                    <a v-if="idx === 0" :href="item.panelUrl" @click.stop
                       style="color:var(--color-text);text-decoration:none;font-weight:500;">{{ displayValue(item, col) }}</a>
                    <span v-else>{{ displayValue(item, col) }}</span>
                  </td>
                </tr>
              </tbody>
            </table>

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
          this.apiEndpoint   = response.resolvedApiEndpoint || 'filtered-files';
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
        }
      },

      template: `
        <section class="k-section k-filteredfiles-section">

          <!-- Header -->
          <header class="k-section-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
            <h2 class="k-headline">{{ headline }}</h2>
          </header>

          <!-- Toolbar: search + filter dropdowns + clear button -->
          <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem;align-items:center;">
            <input
              v-if="searchEnabled"
              type="text"
              v-model="search"
              @input="onSearchInput"
              placeholder="Search..."
              style="flex:1;min-width:160px;padding:0.4rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.875rem;background:var(--color-background);color:var(--color-text);"
            />
            <select
              v-for="field in filterFields"
              :key="field"
              v-model="active[field]"
              @change="onFilterChange"
              style="padding:0.4rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.875rem;background:var(--color-background);color:var(--color-text);cursor:pointer;"
            >
              <option value="">{{ filterDefs[field].label }}: All</option>
              <option v-for="opt in (options[field] || [])" :key="opt.value" :value="opt.value">{{ opt.text }}</option>
            </select>
            <button
              v-if="hasActiveFilters || search"
              @click="resetFilters"
              style="padding:0.35rem 0.65rem;border:1px solid var(--color-border);border-radius:var(--rounded);font-size:0.8rem;background:transparent;color:var(--color-text-dimmed);cursor:pointer;"
            >Clear</button>
          </div>

          <!-- Loading -->
          <div v-if="loading" style="padding:1rem 0;color:var(--color-text-dimmed);font-size:0.875rem;">Loading&hellip;</div>

          <!-- Results table -->
          <template v-else-if="items.length > 0">
            <table style="width:100%;border-collapse:collapse;">
              <thead>
                <tr style="border-bottom:2px solid var(--color-border);">
                  <th style="width:60px;"></th>
                  <th
                    v-for="col in columnDefs"
                    :key="col.field"
                    :style="'text-align:left;padding:0.5rem 0.75rem;font-size:0.75rem;color:var(--color-text-dimmed);font-weight:600;width:' + colWidth(col.width) + ';' + (isSortable(col.field) ? 'cursor:pointer;user-select:none;' : '')"
                    @click="isSortable(col.field) && changeSort(col.field)"
                  >
                    {{ col.label }}<span v-if="isSortable(col.field)" style="opacity:0.6;margin-left:0.25rem;">{{ sortIcon(col.field) }}</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="item in items"
                  :key="item.id"
                  style="border-bottom:1px solid var(--color-border);cursor:pointer;"
                  @click="navigateTo(item.panelUrl)"
                >
                  <td style="padding:0.4rem 0.5rem;width:60px;">
                    <div
                      v-if="item.thumbUrl"
                      :style="'width:48px;height:48px;border-radius:3px;background-image:url(' + JSON.stringify(item.thumbUrl) + ');background-size:cover;background-position:center;'"
                    ></div>
                  </td>
                  <td
                    v-for="(col, idx) in columnDefs"
                    :key="col.field"
                    style="padding:0.6rem 0.75rem;font-size:0.875rem;"
                  >
                    <a v-if="idx === 0" :href="item.panelUrl" @click.stop
                       style="color:var(--color-text);text-decoration:none;font-weight:500;">{{ displayValue(item, col) }}</a>
                    <span v-else>{{ displayValue(item, col) }}</span>
                  </td>
                </tr>
              </tbody>
            </table>

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
    }
  }
});
