<template>
    <div>
        <navigation></navigation>
        <br>
        <div class="jumbotron">
            <h1>{{ title }}</h1>
        </div>

        <h1 v-if="meals.length == 0">
            <center>Theres no meals</center>
        </h1>
        <div v-else>
            <b-pagination align="right" :total-rows="pagination.total" :current="pagination.current_page"
                          :per-page="pagination.per_page" :simple="false" order="is-right"
                          @change="nextPage"></b-pagination>

            <table class="table table-striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>State</th>
                    <th>Responsible</th>
                    <th>Price</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="(meal) in meals">
                    <tr v-bind:class="{'bg-warning' : canShowOrders(meal.id)}">
                        <td>{{ meal.id }}</td>
                        <td>{{ meal.state }}</td>
                        <td>{{ meal.responsible_waiter_id }}</td>
                        <td>{{ `${meal.total_price_preview}€` }}</td>
                        <td>{{ meal.start }}</td>
                        <td>{{ meal.end }}</td>
                        <td>
                            <a class="btn btn-info" v-if="meal.orders.length > 0" @click="rowClicked(meal.id)">Orders</a>
                        </td>

                    </tr>
                    <tr class="bg-dark" v-if="canShowOrders(meal.id)">
                        <td colspan="7">
                            <table class="table table-bordered thead-dark bg-light" style="color: #1b4b72">
                                <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>price</th>
                                    <th>State</th>
                                </tr>
                                </thead>
                                <tbody>

                                <template v-for="order in meal.orders" class="alert-dark">
                                    <tr>
                                        <td>{{order.item_id}}</td>
                                        <td>{{`${order.price}€`}}</td>
                                        <td>{{order.state}}</td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>

    </div>
</template>

<script type="text/javascript">
    import navigation from '../navigation.vue'

    export default {
        components: {
            'navigation': navigation,
        },

        data: function () {
            return {
                title: "List Meals",
                meals: [],
                clickedRow: null,
                showDetails: false,
                pagination: {},
            }
        },
        methods: {
            rowClicked: function (index) {
                if (this.clickedRow == index) {
                    this.showDetails = !this.showDetails;
                } else {
                    this.showDetails = true;
                    this.clickedRow = index;
                }

            },
            getAllMeals(url) {
                if(url == null){
                    url = this.pagination == null ? '/api/meals' : `/api/meals?page=${this.pagination.current_page}`;
                }
                axios.get(url).then(response => {
                    this.makePagination(response.data);
                    this.meals = response.data.data;
                    if (this.meals.length == 0) {
                        this.toastPopUp("show", "Theres no Meals!!!")
                    }
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
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
            },
            nextPage(pageNum) {
                let url = '/api/meals?page=';
                url += pageNum;
                return this.getAllMeals(url);
            },

            canShowOrders: function (index) {
                return this.clickedRow === index && this.showDetails == true;
            },
        },
        mounted() {
            this.getAllMeals();
        },
    }
</script>
