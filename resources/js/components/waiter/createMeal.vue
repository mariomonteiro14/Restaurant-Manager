<template>
	<div>
	<!-- Modal Create Meal -->
		<div class="modal fade" id="addMealModal" tabindex="-1" role="dialog" aria-labelledby="addMealModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMealModal">New Meal</h5>
                        <button type="button" @click="item=null" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="form-group modal-footer">
                        <label for="RestTable">Choose a table:</label>
                        <select class="form-control" id="RestTable" name="Table" v-model="mealTable" required>
                            <option v-for="table in tables" :value="table.table_number">{{table.table_number}}</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-info" v-on:click.prevent="createMeal" :disabled="!mealTable">Add Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
	</div>
</template>

<script type="text/javascript">
	module.exports = {
		props:['item', 'tables'],
		data: function(){
			return{
				mealTable: null,

			}
		},
		methods: {
			createMeal(){
				if (this.mealTable == null) {
                    this.toastPopUp("show", 'Select a Table Number');
                    return;
                }
                axios.post('api/me/meals', {table_number: this.mealTable}).then(res => {
                    this.toastPopUp('success', 'Meal Created');
                    this.$emit('close');
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                });
			},
		},
	}
</script>
