<template>
    <div>
        <navigation></navigation>
        <br>
        <div class="jumbotron">
            <h1>{{ title }}</h1>
        </div>
        <h1 v-if="invoices.length == 0">
            <center>Theres no Invoices</center>
        </h1>
        <div v-else>
            <b-pagination align="right" :total-rows="pagination.total" :current="pagination.current_page"
                          :per-page="pagination.per_page" :simple="false" order="is-right"
                          @change="nextPage"></b-pagination>

            <invoice-list :invoices="invoices" @refresh-invoices="getInvoices"></invoice-list>
        </div>
    </div>
</template>

<script type="text/javascript">
    import navigation from '../navigation.vue';
    import invoiceList from '../cashier/InvoicesList.vue';

    export default {
        components: {
            'navigation': navigation,
            'invoice-list': invoiceList,
        },
        data: function () {
            return {
                title: "Pending Invoices",
                invoices: [],
                pagination: {},
            }
        },
        methods: {
            getInvoices(url) {
                if(url == null){
                    url = this.pagination == null ? 'api/invoices/pending' : `api/invoices/pending?page=${this.pagination.current_page}`;
                }
                axios.get(url).then(response => {
                    this.invoices = response.data.data;
                    this.makePagination(response.data);
                    console.log(this.invoices);
                });
            },
            makePagination(data) {
                let pagination = {
                    total: data.total,
                    current_page: data.current_page,
                    per_page: data.per_page,
                    last_page: data.last_page,
                    next_page_url: data.next_page_url,
                    prev_page_url: data.prev_page_url
                };
                this.pagination = pagination;
                console.log(pagination);
            },
            nextPage(pageNum) {
                let url = '/api/invoices/pending?page=';
                url += pageNum;
                return this.getInvoices(url);
            },

        },
        sockets: {
            new_invoice(){
                this.getInvoices();
            },
            update_invoices(){
                this.getInvoices();
            }
        },
        mounted() {
            this.getInvoices();
        },
        computed: {},
    }
</script>
