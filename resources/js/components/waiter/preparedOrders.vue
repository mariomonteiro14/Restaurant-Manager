<template>
    <div>
        <div>
        <b-pagination v-if="pagination" align="right" :total-rows="pagination.total" :current="pagination.current_page" :per-page="pagination.per_page" :simple="false" order="is-right" @change="nextPage"></b-pagination>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Meal Id</th>
                <th>Item</th>
                <th>State</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <tr v-bind:class="colorOrderState(order)" v-for="order in orders" :key="order.id">
                <td>{{ order.id }}</td>
                <td>{{ order.meal_id }}</td>
                <td>{{ order.item_id }}</td>
                <td>{{ order.state }}</td>
                <td>
                    <a class="btn btn-success" @click="updateState(order, 'delivered')" v-if="order.state=='prepared'">Deliver</a>
                </td>
            </tr>
            </tbody>
        </table>
        </div>
    </div>
</template>

<script type="text/javascript">
    import navigation from '../navigation.vue';
    export default {
        components: {
            'navigation': navigation,
        },
        data: function () {
            return {
                orders: [],
                pagination: null,
            }
        },
        methods: {
            getOrders(url) {
                if(url == null){
                    url = this.pagination == null ? 'api/me/orders/prepared' : `api/me/orders/prepared?page=${this.pagination.current_page}`;
                }
                console.log(url);
                axios.get(url).then(response => {
                    this.makePagination(response.data);
                    this.orders = response.data.data;
                    if (this.orders.length == 0){
                        this.toastPopUp("show","You don't have orders!!!")
                    }
                }).catch(err=>{
                    this.toastPopUp("error",`${err.response.data.message}`)
                });
            },
            makePagination(data){
                let pagination = {
                    total: data.total,
                    current_page: data.current_page,
                    per_page: data.per_page,
                    last_page: data.last_page,
                    next_page_url: data.next_page_url,
                    prev_page_url: data.prev_page_url
                }
               this.pagination = pagination;
            },
            nextPage(pageNum){
                let url = 'api/me/orders/prepared?page=';
                url += pageNum;
                return this.getOrders(url);
            },
            updateState: function(order, state){
                axios.put('api/me/orders/'+order.id, {state: state}).then(res => {
                    this.toastPopUp('success', `Order ${state}`);
                    this.getOrders();
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            cancelOrder: function(order){
                axios.delete('api/me/orders/'+order.id).then(res=>{
                    this.getOrders();
                    this.toastPopUp("success", "Order Canceled");
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },

            colorOrderState: function(order){
                if (order.state == "prepared"){
                    return "alert-success";
                }
                if (order.state == "not delivered"){
                    return "alert-danger";
                }
                if (order.state == "delivered"){
                    return "alert-dark";
                }

            }
        },
        sockets: {
            prepared_order_waiter(){
                this.getOrders();
            },
            update_list(){
                this.getOrders();
            }
        },
        mounted() {
            this.getOrders();
        },
    }
</script>
