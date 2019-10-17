<template>
	<div>
		<!-- Modal Terminate Meal -->
		<div class="modal fade" id="terminateRequest" tabindex="-1" role="dialog" aria-labelledby="terminateRequest"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="terminateRequest">There are still orders not delivered. Do you want
                            to procced?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="terminate(currentMeal)">Yes</button>
                        <button class="btn dg-btn--cancel" data-dismiss="modal" aria-label="Close">No</button>
                    </div>
                </div>
            </div>
        </div>
	</div>
</template>
<script type="text/javascript">
	module.exports = {
		props:['currentMeal'],
		methods: {
			terminate: function (meal) {
                axios.put('api/me/meals/' + meal.id, {
                    state: 'terminated',
                    cancelNotDeliveredOrders: true
                }).then(res => {
                    this.toastPopUp('success', 'Meal Terminated');
                    meal.state = 'terminated';
                    this.currentMeal = null;
                    let notification = {
                        from: this.$store.state.user.name,
                        to: "all cashiers",
                        message: "New invoice available",
                        url: "#/invoices/pending",
                        date: new Date().toUTCString(),
                    }
                    this.$socket.emit('new_invoice', notification);
                    notification.to = "all managers";
                    notification.message = `Meal #${meal.id} terminated`;
                    notification.url = "#/notifications";
                    this.$socket.emit('meal_end', notification);
                    this.$emit('update');
                    $('#terminateRequest').modal('hide');
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            terminateMeal: function (meal) {
                axios.get('api/me/meals/' + meal.id + '/ordersNotDelivered').then(res => {
                    let ordersNotDelivered = [];
                    ordersNotDelivered = res.data;
                    console.log(ordersNotDelivered);
                    if (ordersNotDelivered.length > 0) {
                    	this.$emit('newCurrentMeal', meal);
                        $('#terminateRequest').modal('show');
                    } else {
                        this.terminate(meal);
                    }
                });
            },
		},
	}
</script>