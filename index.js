panel.plugin('open-foundations/kirby-base', {
  sections: {
    externalfiles: {
      template: `
                <k-section :headline="headline">
                    <k-collection
                        :items="items"
                        layout="list"
                    >
                        <template slot="info" slot-scope="item">
                            <a :href="item.url" target="_blank" rel="noopener noreferrer" class="k-link">
                                Download ({{ item.info }})
                            </a>
                        </template>
                    </k-collection>
                </k-section>
            `,
      props: {
        headline: String,
        path: String,
        items: Array
      },
      data() {
        return {
          items: []
        };
      },
      created() {
        // Fetch data from the API endpoint defined in PHP
        this.load().then(response => {
          this.items = response.items;
        });
      }
    }
  }
});
