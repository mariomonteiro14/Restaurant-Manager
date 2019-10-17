<template>
	<div>
	<!-- Modal Login-->
	    <div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModal" aria-hidden="true">
	      <div class="modal-dialog" role="document">
	        <div class="modal-content">
	          <div class="modal-header">
	            <h5 class="modal-title" id="loginModal">Login</h5>
	            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	              <span aria-hidden="true">&times;</span>
	            </button>
	          </div>
	            <div class="modal-body">
	              <div class="container box">
	                <div class="form-group">
	                  <label id="emailLabel" for="inputEmailUsername">Email/Username</label>
	                  <input
	                      type="text" class="form-control" v-model.trim="user.userOrEmail"
	                      name="emailUsername" id="inputEmailUsername"
	                      placeholder="Email address or Username"/>
	                </div>
	                <div class="form-group">
	                  <label for="inputPassword">Password</label>
	                  <input
	                      type="password" class="form-control" v-model="user.password"
	                      name="password" id="inputPassword"/>
	                </div>
	              </div>
	            </div>
	            <div class="modal-footer">
	              <button class="btn btn-info" v-on:click.prevent="login" :disabled="!formCompleted">Login</button>
	            </div>
	        </div>
	      </div>
	    </div>
	</div>
</template>

<script type="text/javascript">
	module.exports={
		data: function(){
      		return {
        		user: {
          			userOrEmail: "",
          			password:""
        		},
        	}
        },
		methods: {
			login() {
		        axios.post('api/login', this.user).then(response => {
		        	this.$store.commit('setToken',response.data.access_token);
		          	return axios.get('api/users/me');
		        }).then(response => {
		          	this.$store.commit('setUser',response.data.data);
		          	this.$socket.emit('user_login', response.data.data);
		          	this.toastPopUp("success", `Welcome ${this.$store.state.user.name}`);
		          	$('#loginModal').modal('hide');
		        }).catch(error => {
		          	this.$store.commit('clearUserAndToken');
		          	if(error.response.status && error.response.status === 401){
		            	this.toastPopUp("error", `${error.response.data.message}`);
		          	}else{
		            	this.toastPopUp("error", `${error.response.data.message}`);
		          	}
		        })
		    },
		},
		computed: {
		    formCompleted(){
			    return this.user.userOrEmail && this.user.password;
			}
		},
	}
</script>