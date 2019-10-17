<template>
    <div class="modal fade" id="PayModal" tabindex="-1" role="dialog" aria-labelledby="PayModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="PayModal">Pay Meal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container box">
                        <div class="form-group">
                            <label id="nameLabel" for="inputName">Name:</label>
                            <input
                                type="text" class="form-control" v-model="name"
                                name="inputName" id="inputName"
                                required placeholder="Client's name"/>
                        </div>
                        <div class="form-group">
                            <label id="nifLabel" for="inputNif">NIF:</label>
                            <input
                                type="number" class="form-control" v-model="nif"
                                min="100000000" max="999999999"
                                name="inputNif" id="inputNif"
                                required placeholder="Client's NIF"/>
                        </div>
                        <div class="form-group">
                            <label id="priceLable">Amount to Pay: {{invoice.total_price}}€</label>
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-info" v-on:click.prevent="payInvoice" :disabled="!formCompleted">Pay</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">

    module.exports = {
        props: ['invoice'],
        data: function () {
            return {
                nif: null,
                name: null,
            }
        },
        methods: {
            payInvoice: function () {
                if (this.nif == null || this.name == null) {
                    this.toastPopUp("show", 'Fill all the fields');
                    return;
                }
                console.log(this.name + this.nif);
                axios.put('api/invoices/' + this.invoice.id, {
                    nif: this.nif,
                    name: this.name,
                    state: "paid"
                }).then(res => {
                    this.toastPopUp('success', 'Meal Paid');
                    this.invoice.state = 'paid';
                    let notification = {
                        from: this.$store.state.user.name,
                        to: "all managers",
                        message: "Meal paid",
                        url: "#/notifications",
                        date: new Date().toUTCString(),
                    }
                    this.$socket.emit('meal_end', notification);
                    this.$emit('close',this.invoice);
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                });
            },
            formCompleted: function () {
                return this.nif != null && this.name != null;
            }
        },
        mounted() {
            this.invoice = this.$emit('currentInvoice')
        },
        computed: {},
    }
</script>
