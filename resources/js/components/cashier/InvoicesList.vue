<template>
    <div>
        <table class="table">
            <thead>
            <tr>
                <th>Meal ID</th>
                <th>Table</th>
                <th>Total Price</th>
                <th>Waiter Responsible</th>
                <th>State</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <template v-for="invoice in invoices">
            <tr :class="colorInvoiceState(invoice)">
                <td>{{ invoice.meal_id }}</td>
                <td>{{ invoice.meal.table_number }}</td>
                <td>{{ `${invoice.total_price}€` }}</td>
                <td>{{ invoice.meal.responsible_waiter_id }}</td>
                <td>{{ invoice.state }}</td>
                <td>
                    <a class="btn btn-info" @click="rowClicked(invoice.id)"><font color="#00008b">Show Items</font></a>
                    <a class="btn btn-success" v-if="invoice.state == 'pending'" @click="setupPay(invoice)"><font color="#006400">Pay</font></a>
                    <a class="btn btn-danger" v-if="invoice.state == 'paid'" @click="downloadInvoice(invoice)"><font color="#FFFFFF">Download</font></a>
                </td>
            </tr>
            <tr class="bg-light" v-if="canShowDetails(invoice.id)">
                <td colspan="6">
                    <table class="table table-bordered thead-dark bg-light" style="color: #1b4b72">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subprice</th>
                        </tr>
                        </thead>
                        <tbody>

                        <template v-for="item in invoice.items" class="alert-dark">
                            <tr>
                                <td>{{item.item_id}}</td>
                                <td>{{item.quantity}}</td>
                                <td>{{item.unit_price}} €</td>
                                <td>{{item.sub_total_price}} €</td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </td>
            </tr>
            </template>
            </tbody>
        </table>
        <pay-meal v-bind:invoice="currentInvoice" @close="closePay"></pay-meal>
    </div>
</template>



<script type="text/javascript">
    import payMeal from './PayMeal.vue';
    import jsPDF from 'jspdf'

    export default {
        components: {'pay-meal': payMeal},
        props: ['invoices'],

        data: function () {
            return {
                title: "Pending Invoices",
                clickedRow: null,
                showDetails: false,
                currentInvoice: {},
            }
        },
        methods: {
            rowClicked: function (index) {
                if (this.clickedRow === index) {
                    this.showDetails = !this.showDetails;
                } else {
                    this.showDetails = true;
                }
                this.clickedRow = index;
            },
            canShowDetails: function(id){
                return this.showDetails && this.clickedRow === id;
            },
            setupPay: function(invoice){
                this.currentInvoice = invoice;
                $('#PayModal').modal('show');
                console.log("setupPay")
            },
            closePay: function(){
                $('#PayModal').modal('hide');
                this.currentInvoice = {};

                setTimeout(function () { this.$emit('refresh-invoices') }.bind(this), 2000);
            },
            colorInvoiceState: function (invoice) {
                if (invoice.state == "pending") {
                    return "alert-warning"
                }
                if (invoice.state == "paid") {
                    return "alert-success";
                }
                if (invoice.state == "not paid") {
                    return "alert-danger";
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
        },
        computed: {},
    }
</script>
