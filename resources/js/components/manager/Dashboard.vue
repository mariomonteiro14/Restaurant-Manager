<template>
    <div>
        <navigation></navigation>
        <br>

        <h1 v-if="meals.length == 0">
             <center>Theres no meals and Invoices</center>
         </h1>
        <div>
            <b-pagination align="right" :total-rows="pagination.total" :current="pagination.current_page"
                          :per-page="pagination.per_page" :simple="false" order="is-right"
                          @change="nextPage"></b-pagination>

            <table class="table">
                <thead>
                <tr>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Table Number</th>
                    <th>Responsible</th>
                    <th>Current Amount</th>
                    <th>State</th>
                    <th colspan="3">Action</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="meal in meals">
                    <tr v-bind:class="getMealColor(meal)">
                        <td>Meal</td>
                        <td>{{ meal.id }}</td>
                        <td>{{ meal.table_number }}</td>
                        <td>{{ meal.responsible_waiter_id }}</td>
                        <td>{{ `${meal.total_price_preview}€`}}</td>
                        <td>{{ meal.state }}</td>
                        <td colspan="3">
                            <a class="btn btn-info" v-if="meal.orders.length > 0" @click="rowClicked(meal.id)">Orders</a>
                            <a class="btn btn-danger" v-if="meal.state == 'terminated'" @click="setNotPaid(meal)">Not Paid</a>
                        </td>
                    </tr>
                    <tr class="bg-dark" v-if="canShowOrders(meal.id)">
                        <td colspan="8">
                            <table class="table table-bordered thead-dark bg-light" style="color: #1b4b72">
                                <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Price</th>
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
                    <tr class="bg-success" v-if="meal.invoices!=null">
                        <td>Invoice</td>
                        <td>{{meal.invoices.id}}</td>
                        <td>{{ meal.table_number }}</td>
                        <td>-</td>
                        <td>{{ `${meal.invoices.total_price}€` }}</td>
                        <td>{{ meal.invoices.state }}</td>
                        <td>
                            <a class="btn btn-danger" @click="setNotPaid(meal)">Not Paid</a>
                            <a v-if="meal.invoices.state == 'paid'" class="btn btn-danger" @click="downloadInvoice(meal.invoices)">Download</a>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>

    </div>
</template>

<script type="text/javascript">
    import navigation from '../navigation.vue';
    import jsPDF from 'jspdf'

    export default {
        components: {
            'navigation': navigation,
        },
        data: function () {
            return {
                title: "List Meals",
                meals: [],
                currentMeal: null,
                clickedRow: null,
                showDetails: false,
                pagination: {},
            }
        },
        methods: {
            rowClicked: function (index) {
                if (this.clickedRow === index) {
                    this.showDetails = !this.showDetails;
                } else {
                    this.showDetails = true;
                    this.clickedRow = index;
                }

            },
            getMeals(url) {
                url = url || '/api/manager/meals';
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
            canShowOrders: function (index) {
                return this.clickedRow === index && this.showDetails === true;
            },
            setNotPaid: function (meal) {
                axios.put("/api/me/meals/" + meal.id, {state: "not paid"}).then(res => {
                    this.toastPopUp("success", "Meal Updated");
                    meal.state = "not paid";
                    this.$socket.emit('not_paid');

                    if (meal.invoices != null) {
                        axios.put("/api/invoices/" + meal.invoices.id, {state: "not paid"}).then(resp => {
                            meal.invoices.state = "not paid";
                            this.toastPopUp("success", "Meal Invoices Updated");
                        }).catch(error => {
                            this.toastPopUp("error", `${error.response.data.message}`);
                        });
                    }
                    setTimeout(this.getMeals(), 3000);
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
                console.log(pagination);
            },
            nextPage(pageNum) {
                let url = '/api/manager/meals?page=';
                url += pageNum;
                return this.getMeals(url);
            },
            getMealColor: function (meal) {
                if (meal.state == "active") {
                    return "bg-light"
                }
                if (meal.state == "terminated") {
                    return "bg-warning"
                }
                if (meal.state == "not paid") {
                    return "bg-danger"
                }
            },
            downloadInvoice: function(invoice){
                var doc = new jsPDF();
                let height = 0;
                doc.setFontSize(40);
                doc.text(65, height+=25, `Invoice #${invoice.id}`);
                doc.setFontSize(16);
                doc.fromHTML('<b>Meal</b>: #' + invoice.meal_id, 20, height+=10);
                doc.fromHTML(`<b>Date</b>: ${invoice.date}`, 20, height+=10);
                doc.fromHTML(`<b>State</b>: ${invoice.state}`, 20, height+=10);
                if(invoice.state == "paid"){
                    doc.setFontType()
                    doc.fromHTML(`<b>Total</b>: ${invoice.total_price} euros`, 20, height+=10);
                }
                if(invoice.name && invoice.nif){
                    doc.fromHTML(`<b>Client's name</b>: ${invoice.name}`, 20, height+=10);
                    doc.fromHTML(`<b>Client's NIF</b>: ${invoice.nif}`, 20, height+=10);
                }
                doc.save(`Invoice #${invoice.id}`);
            },
        },
        mounted() {
            this.getMeals();
        },
    }
</script>
