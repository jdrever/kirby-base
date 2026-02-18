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
    }
  }
});
