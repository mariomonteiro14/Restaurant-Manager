<template>
	<div>
	<!-- Modal Add Order-->
        <div class="modal fade" id="addOrderModal" tabindex="-1" role="dialog" aria-labelledby="addOrderModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addOrderModal">New Order</h5>
                        <button type="button" @click="item=null" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="form-group modal-footer">
                        <label for="Type">Type:</label>
                        <select class="form-control" id="type" name="type" v-model="type" required>
                            <option value="dish">dish</option>
                            <option value="drink">drink</option>
                        </select>
                    </div>
                    <div class="form-group modal-footer">
                        <label for="item">Item:</label>
                        <select class="form-control" id="item" name="item" v-model="selectedItem" required>
                            <option v-if="type=='dish'" v-for="item in dishesOnly" :value="item">{{item.name}}</option>
                            <option v-if="type=='drink'" v-for="item in drinksOnly" :value="item">{{item.name}}</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="addOrder" :disabled="!selectedItem">Add Order</button>
                    </div>
                </div>

            </div>
        </div>
	</div>
</template>

<script type="text/javascript">
	module.exports = {
		props:['items', 'item', 'currentMeal'],
		data: function(){
			return {
				type: 'dish',
				selectedItem: null,
			}
		},
		methods: {
			addOrder: function () {
                axios.post('api/me/meals/' + this.currentMeal.id + "/orders", {item_id: this.selectedItem.id}).then(response => {
                    this.toastPopUp('success', 'Order Created. You have 5 seconds to cancel it');
                    this.$emit('set-timeout', response.data);
                    this.$emit('refresh-meals');
                    this.$emit('reset-variables');
                    $('#addOrderModal').modal('hide');
                    console.log("here");
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                });
            },
		},
		computed: {
			drinksOnly: function () {
                var drinks = [];
                for (let i in this.items) {
                    if (this.items[i].type == "drink") {
                        drinks.push(this.items[i]);
                    }
                }
                return drinks;
            },
            dishesOnly: function () {
                var dishes = [];
                for (let i in this.items) {
                    if (this.items[i].type == "dish") {
                        dishes.push(this.items[i]);
                    }
                }
                return dishes;
            },
		},
	}
</script>