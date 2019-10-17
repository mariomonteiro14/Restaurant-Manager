<template>
	<div>
		<navigation></navigation>
        <br/>
    	<b-pagination align="right" :total-rows="pagination.total" :current="pagination.current_page"
                      :per-page="pagination.per_page" :simple="false" order="is-right"
                      @change="nextPage"></b-pagination>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>ID</th>
					<th>State</th>
					<th>Item</th>
					<th>Meal</th>
					<th>Responsible Cook</th>
					<th>Started at</th>
					<th>Finished at</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<template v-for= "order in orders">
					<tr v-bind:class="{'bg-warning': order.state == 'in preparation'}">
						<td>{{order.id}}</td>
						<td>{{order.state}}</td>
						<td>{{order.item_id}}</td>
						<td>{{order.meal_id}}</td>
						<td>{{order.responsible_cook_id}}</td>
						<td>{{order.start}}</td>
						<td>{{order.end}}</td>
						<td v-if="order.state == 'confirmed'">
							<a class="btn btn-info" v-on:click="startPreparing(order)">Start preparing</a>
                            <a class="btn btn-success" v-on:click="prepared(order)">Prepared</a>
						</td>
						<td v-if="order.state == 'in preparation'">
							<a class="btn btn-success" v-on:click="prepared(order)">Finish</a>
						</td>

					</tr>
				</template>
			</tbody>
		</table>
	</div>
</template>

<script type="text/javascript">
	import navigation from '../navigation.vue';
	export default {
		components: {
			'navigation': navigation,
		},
		data: function(){
			return {
				orders: [],
				pagination: {},
			}
		},
		methods: {
			getOrders(url){
				if(url == null){
                    url = this.pagination == null ? 'api/cook/orders' : `/api/cook/orders?page=${this.pagination.current_page}`;
                }
				axios.get(url).then(response => {
					this.makePagination(response.data);
					this.orders = response.data.data;
					if(this.orders.length == 0){
					    this.toastPopUp("show", "Theres no orders");
                    }
				}).catch(error =>{
					this.toastPopUp("error", "Error while getting orders");
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
                let url = '/api/cook/orders?page=';
                url += pageNum;
                return this.getAllMeals(url);
            },
			startPreparing(order){
				axios.put(`api/me/orders/${order.id}`, {responsible_id: this.$store.state.user.id, state: "in preparation"}).then(response => {
                    this.getOrders();
                    this.$socket.emit('update', response.data.responsible_waiter_id);
					this.toastPopUp("success", "Order in preparation");
				})
			},
			prepared(order){
				axios.put(`api/me/orders/${order.id}`, {responsible_id: this.$store.state.user.id, state: "prepared"}).then(response => {
					let notification = {
						from: this.$store.state.user.name,
						to: response.data.responsible_waiter_id,
						message: `Order #${order.id} prepared!`,
						url: "#/waiter/orders",
						date: new Date().toUTCString(),
					}
					this.$socket.emit('prepared_order', notification, response.data.responsible_waiter_id);
                    this.toastPopUp("success", "Order prepared");
					this.getOrders();
				}).catch(error => {
					this.toastPopUp("error", "Error while setting order as prepared");
				})
			},
		},
		sockets: {
			new_order_cooks(){
				this.getOrders();
			}
		},
		mounted(){
			this.getOrders();
		},
	}
</script>
