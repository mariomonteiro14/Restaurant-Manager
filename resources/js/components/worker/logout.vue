<template>
	<div>
	<!-- Modal Logout-->
	    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModal" aria-hidden="true">
	      <div class="modal-dialog" role="document">
	        <div class="modal-content">
	          <div class="modal-header">
	            <h5 class="modal-title" id="logoutModal">Logout</h5>
	            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	              <span aria-hidden="true">&times;</span>
	            </button>
	          </div>
	          <div class="modal-body">
	            Are you sure you want to logout?
	          </div>
	          <div class="modal-footer">
	            <button type="button" class="btn btn-primary" v-on:click.prevent="logout">Yes</button>
	            <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
	          </div>
	        </div>
	      </div>
	    </div>
	</div>
</template>

<script type="text/javascript">
	module.exports = {
		methods: {
			logout() {
		    	axios.get('api/logout').then(response => {
		    		this.$socket.emit('user_logout', this.$store.state.user);
		        	this.$store.commit('clearUserAndToken');
		        	this.toastPopUp("success", "Logged out");
		        	$('#logoutModal').modal('hide');
		        	this.$router.push({name: 'menu'});
		        }).catch(error => {
		        	this.$store.commit('clearUserAndToken');
		        	this.toastPopUp("error", "Error on logout");
		        	console.log(error);
		    	})
		    },
		},
	}
</script>