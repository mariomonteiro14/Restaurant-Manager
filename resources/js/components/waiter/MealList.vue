<template>
    <div>
        <navigation></navigation>
        <br>
        <div class="jumbotron">
            <h1>{{ title }}</h1>
        </div>
        <div class="form-inline">
            <a class="btn btn-primary" v-on:click.prevent="showCreate">Create New Meal</a>
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
                    <th>Table Number</th>
                    <th>Current Amount</th>
                    <th>State</th>
                    <th colspan="2">Action</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="(meal) in meals">
                    <tr v-bind:class="{'bg-warning' : canShowOrders(meal.id)}">
                        <td>{{ meal.id }}</td>
                        <td>{{ meal.table_number }}</td>
                        <td>{{ meal.total_price_preview }}</td>
                        <td colspan="2">{{ meal.state }}</td>
                        <td>
                            <p v-if="meal.state!='active'">Done</p>
                            <a v-if="meal.state=='active'" @click="setupAddOrder(meal)" class="btn btn-success"
                               data-toggle="modal"
                               data-target="#addOrderModal">Add Order</a>
                            <a class="btn btn-info" v-if="meal.total_price_preview > 0 && meal.state=='active'"
                               @click="rowClicked(meal.id)">Show Items Ordered</a>
                            <a v-if="meal.state=='active'" class="btn btn-danger"
                               @click="$refs.terminate.terminateMeal(meal)">Terminate</a>
                        </td>
                    </tr>
                    <tr class="bg-dark" v-if="canShowOrders(meal.id)">
                        <td colspan="6">
                            <table class="table table-bordered thead-dark bg-light" style="color: #1b4b72">
                                <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>price</th>
                                </tr>
                                </thead>
                                <tbody>

                                <template v-for="item in items" class="alert-dark">
                                    <tr>
                                        <td>{{item.name}}</td>
                                        <td>{{item.price}} €</td>
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

        <!-- Modal Add Order-->
        <add-order v-bind:items="items" v-bind:item="item" v-bind:currentMeal="currentMeal" @refresh-meals="getAllMeals" @reset-variables="resetVariables" @set-timeout="startTimeout"></add-order>

        <!-- Modal Create Meal -->
        <create-meal v-bind:item="item" v-bind:tables="tables" @close="closeAdd"></create-meal>

        <!-- Modal Terminate Meal -->
        <terminate-meal v-bind:currentMeal="currentMeal" @newCurrentMeal="currentMeal = $event" ref="terminate" @update="getAllMeals"></terminate-meal>

    </div>
</template>

<script type="text/javascript">
    import navigation from '../navigation.vue';
    import addOrder from './addOrder.vue';
    import createMeal from './createMeal.vue';
    import terminateMeal from './terminateMeal.vue';

    export default {
        components: {
            'navigation': navigation,
            'add-order': addOrder,
            'create-meal': createMeal,
            'terminate-meal': terminateMeal,
        },
        data: function () {
            return {
                title: "List Meals",
                meals: [],
                requiredMeals: [],
                tables: [],
                mealTable: null,
                currentMeal: null,
                items: [],
                item: null,
                clickedRow: null,
                showDetails: false,
                pagination: {},
                mealsLoop: false,
            }
        },
        methods: {
            rowClicked: function (index) {
                if (this.clickedRow == index) {
                    this.showDetails = !this.showDetails;
                } else {
                    this.mealOrders = [];
                    this.getMealOrders(index);
                    this.showDetails = true;
                }
                this.clickedRow = index;
            },
            getItems: function () {
                axios.get('api/items').then(response => {
                    this.items = response.data.data;
                });
            },
            getAllMeals(url) {
                if(url == null){
                    url = this.pagination == null ? '/api/me/meals' : `/api/me/meals?page=${this.pagination.current_page}`;
                }
                axios.get(url).then(response => {
                    //console.log(response.data);
                    this.makePagination(response.data);
                    this.meals = response.data.data;
                    if (this.meals.length == 0) {
                        this.toastPopUp("show", "Theres no Meals!!!")
                    }
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                });
            },
            closeAdd: function () {
                $('#addMealModal').modal('hide');
                this.getAllMeals();
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
                let url = '/api/me/meals?page=';
                url += pageNum;
                return this.getAllMeals(url);
            },
            getMealOrders: function (index) {
                this.items = [];
                axios.get('api/me/meals/' + index + '/items').then(res => {
                    this.items = res.data;
                    if (this.items.length == 0) {
                        this.toastPopUp("show", "This meal has no orders");
                    }
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            canShowOrders: function (index) {
                return this.clickedRow === index && this.showDetails == true && this.items.length > 0;
            },
            resetVariables: function () {
                this.currentMeal = null;
                this.item = null;
                this.items = [];
            },
            startTimeout: function (data) {
                setTimeout(this.confirmOrder, 5000, data);
            },
            showCreate: function () {
                axios.get('api/tablesAvailables').then(res => {
                    this.tables = res.data.data;
                    if (this.tables.length > 0) {
                        $('#addMealModal').modal('show');
                    } else {
                        this.toastPopUp('show', 'Theres no tables available');
                    }
                })
            },
            setupAddOrder: function (meal) {
                this.showDetails = false;
                this.getItems();
                this.currentMeal = meal;
            },
            confirmOrder: function (order) {
                axios.get('api/me/orders/' + order.id + '/confirm').then(res => {
                    if (res.data.state) {
                        if (res.data.state == "confirmed") {
                            let notification = {
                                from: this.$store.state.user.name,
                                to: "all cooks",
                                message: "New order arrived!",
                                url: "#/cook/orders",
                                date: new Date().toUTCString(),
                            }
                            this.$socket.emit('new_order', notification);
                            this.toastPopUp('success', `Order #${order.id} confirmed`);
                        }
                    }
                }).catch(error => {
                    this.toastPopUp("error", "Error while confirming order");
                });
            }
        },
        mounted() {
            this.getAllMeals();
        },
    }
</script>
